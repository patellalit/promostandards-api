<?php

namespace Shera\PromostandardsApi;

class Client {

    private $id;
    private $password;
    private $output;

    public function __construct($id, $password, $output = "json") {
        $this->id = $id;
        $this->password = $password;
        $this->output = $output;
    }

    public function getProductDataV2($endpoint, $version, $productId, $localizationCountry, $localizationLanguage, $extraParams = []) {
        $params = [
            'wsVersion' => $version,
            'id' => $this->id,
            'password' => $this->password,
            'productId' => $productId,
            'localizationCountry' => $localizationCountry,
            'localizationLanguage' => $localizationLanguage
        ];

        if(!empty($extraParams)) {
            $params = array_merge($params, $extraParams);
        }
        $xmlBody = $this->generateXml($params);

        $xmlRequest = <<<XML
<x:Envelope
    xmlns:x="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ns="http://www.promostandards.org/WSDL/ProductDataService/2.0.0/"
    xmlns:sha="http://www.promostandards.org/WSDL/ProductDataService/2.0.0/SharedObjects/">
    <x:Header/>
    <x:Body>
        <ns:GetProductRequest>
            $xmlBody
        </ns:GetProductRequest>
    </x:Body>
</x:Envelope>
XML;

        $productData = $this->callSOAPXml($endpoint, $xmlRequest, 'getProduct');
        return $productData;
    }

    function generateXml($params, $namespace = 'sha') {
        $xml = '';
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $xml .= "<{$namespace}:{$key}>" . $this->generateXml($value, $namespace) . "</{$namespace}:{$key}>";
            } else {
                $xml .= "<{$namespace}:{$key}>{$value}</{$namespace}:{$key}>";
            }
        }
        return $xml;
    }

    public function callSOAPXml($endpoint, $xmlRequest, $SOAPAction) {
        
        $headers = [
            'Content-Type: text/xml; charset=UTF-8',
            'Soapaction: ' . $SOAPAction
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);

        #SSL OFF
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);

        #check CURL ERROR
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return $error_msg;
        }

        //return XML
        if($this->output == "xml") {
            return $response;
        }

        //return JSON array
        $plainXML = $this->parseXMLtoJSON( trim($response) );
        return json_decode(json_encode(SimpleXML_Load_String($plainXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    protected function parseXMLtoJSON($xml)
    {
        $obj = SimpleXML_Load_String($xml);
        if ($obj === FALSE) return $xml;

        // GET NAMESPACES, IF ANY
        $nss = $obj->getNamespaces(TRUE);
        if (empty($nss)) return $xml;

        // CHANGE ns: INTO ns_
        $nsm = array_keys($nss);
        foreach ($nsm as $key)
        {
            // A REGULAR EXPRESSION TO MUNG THE XML
            $rgx
            = '#'               // REGEX DELIMITER
            . '('               // GROUP PATTERN 1
            . '\<'              // LOCATE A LEFT WICKET
            . '/?'              // MAYBE FOLLOWED BY A SLASH
            . preg_quote($key)  // THE NAMESPACE
            . ')'               // END GROUP PATTERN
            . '('               // GROUP PATTERN 2
            . ':{1}'            // A COLON (EXACTLY ONE)
            . ')'               // END GROUP PATTERN
            . '#'               // REGEX DELIMITER
            ;
            // INSERT THE UNDERSCORE INTO THE TAG NAME
            $rep
            = '$1'          // BACKREFERENCE TO GROUP 1
            . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
            ;
            // PERFORM THE REPLACEMENT
            $xml =  preg_replace($rgx, $rep, $xml);

            // remove namespace from key with _
            $xml = str_replace($key . '_', '', $xml);
        }

        return $xml;
    }
}
<?php

namespace SouthCoast\Helpers\Objects;

use SimpleXMLElement;

class XmlObject
{
    private $xml;

    public function __construct(string $openingTag, string $version = '1.0')
    {
        $xmlOpeningConfig = '<?xml version="' . $version . '"?><' . $openingTag . '></' . $openingTag . '>';
        $this->xml = new SimpleXMLElement($xmlOpeningConfig);

        return $this;
    }

    public function loadFromString(string $xml, bool $isUrl = false)
    {
        $this->xml = new SimpleXMLElement($xml, LIBXML_COMPACT | LIBXML_PARSEHUGE, $isUrl);

        return $this;
    }

    public function loadArray(array $data, $node = null)
    {
        /* if subnode isn't declaired, make it this->xml */
        if ($node === null) {
            $node = $this->xml;
        }

        /* Loop through all data items recursively */
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $node->addChild($key);
                $this->fromArray($value, $subnode);
            } else {
                $node->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $this;
    }

    public function getXml(): string
    {
        return $this->xml->asXml();
    }

    public function getArray(): array
    {
        return ArrayHelper::sanitize($this->xml);
    }

    public function __toString()
    {
        return $this->getXml();
    }
}

<?php

namespace WsdlToPhp\PackageGenerator\Parser\Wsdl;

use WsdlToPhp\PackageGenerator\WsdlHandler\Tag\AbstractTag;
use WsdlToPhp\PackageGenerator\WsdlHandler\Wsdl as WsdlDocument;
use WsdlToPhp\PackageGenerator\WsdlHandler\Tag\TagChoice as Choice;
use WsdlToPhp\PackageGenerator\Model\Wsdl;
use WsdlToPhp\PackageGenerator\Model\Struct;
use WsdlToPhp\PackageGenerator\Model\StructAttribute;

class TagChoice extends AbstractTagParser
{
    /**
     * @see \WsdlToPhp\PackageGenerator\Parser\Wsdl\AbstractParser::parseWsdl()
     * @param Wsdl $wsdl
     */
    protected function parseWsdl(Wsdl $wsdl)
    {
        foreach ($this->getTags() as $tag) {
            if ($tag instanceof Choice) {
                $this->parseChoice($tag);
            }
        }
    }

    /**
     * @see \WsdlToPhp\PackageGenerator\Parser\Wsdl\AbstractParser::parsingTag()
     */
    protected function parsingTag()
    {
        return WsdlDocument::TAG_CHOICE;
    }

    /**
     * @see https://www.w3schools.com/xml/el_choice.asp
     * @see https://www.w3.org/TR/xmlschema11-1/#element-choice
     * @param Choice $choice
     */
    public function parseChoice(Choice $choice)
    {
        $parent = $choice->getSuitableParent();
        $children = $choice->getChildrenElements();
        if ($parent && count($children) && ($struct = $this->getModel($parent)) instanceof Struct) {
            $unionNames = [];
            foreach ($children as $child) {
                $unionNames[] = $child->getAttributeName();
            }
            foreach ($children as $child) {
                $this->parseChoiceChild($choice, $unionNames, $child, $struct);
            }
            unset($unionNames);
        }
    }

    /**
     * @param Choice $choice
     * @param array $unionNames
     * @param AbstractTag $child
     * @param Struct $struct
     */
    protected function parseChoiceChild(Choice $choice, array $unionNames, AbstractTag $child, Struct $struct)
    {
        if (($structAttribute = $struct->getAttribute($child->getAttributeName())) instanceof StructAttribute) {
            $structAttribute
                ->addMeta('choiceNames', $unionNames)
                ->addMeta('choiceMaxOccurs', $choice->getMaxOccurs())
                ->addMeta('choiceMinOccurs', $choice->getMinOccurs());
        }
    }
}

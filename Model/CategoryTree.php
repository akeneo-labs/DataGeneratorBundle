<?php

namespace Pim\Bundle\DataGeneratorBundle\Model;

class CategoryTree
{
    /** @var  string */
    protected $code;
    /** @var  string[] */
    protected $labels;
    /** @var  CategoryTree[] */
    protected $children;
    /** @var  int */
    protected $level;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param string $locale
     * @param string $label
     */
    public function addLabel($locale, $label)
    {
        $this->labels[$locale] = $label;
    }

    /**
     * @return CategoryTree[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $code
     * @param int    $level
     */
    public function __construct($code, $level)
    {
        $this->code = trim($code);
        $this->level = (int)$level;
        $this->labels = [];
        $this->children = [];
    }

    /**
     * @param CategoryTree $child
     */
    public function addChild(CategoryTree $child)
    {
        $this->children[] = $child;
    }

    /**
     * @return string[]
     */
    public function flatten()
    {
        $flat = [
            'code'   => $this->getCode(),
            'parent' => '',
        ];
        foreach ($this->getLabels() as $locale => $label) {
            $flat['label-'.$locale] = $label;
        }

        return $flat;
    }
}

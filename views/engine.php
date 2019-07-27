<?php

class Views
{
    private $tags = [];
    private $template;

    public function __construct($file, $dest = '', $token = '')
    {
        $this->template = $this->getFile('views/' . $file . '.tpl');
        if (!$this->template) {
            return "Error! Can't load the template file $file";
        } else {
            $this->set('dest', $dest);
            $this->set('token', $token);
        }
    }

    public function loadFile($file)
    {
        $this->template = $this->getFile('views/' . $file . '.tpl');
        if (!$this->template) {
            return "Error! Can't load the template file $file";
        } else {
            return $this;
        }
    }

    public function render()
    {
        echo $this->prepare();
    }

    public function prepare()
    {
        $this->replaceTags();
        return $this->template;
    }

    public function set($tag, $value)
    {
        $this->tags[$tag] = $value;
        return $this;
    }

    public function getFile($file)
    {
        if (file_exists($file)) {
            $file = file_get_contents($file);
            return $file;
        } else {
            return false;
        }
    }

    private function replaceTags()
    {
        foreach ($this->tags as $tag => $value) {
            $this->template = str_replace('{'.$tag.'}', $value, $this->template);
        }
        return true;
    }

    public function loadAndRender($file)
    {
        $this->loadFile($file);
        $this->render();
    }
}

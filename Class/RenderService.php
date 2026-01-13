<?php

class RenderService {

    private $title;
    private $contenttop;
    private $contentbottom;

    private $buttonvalue;
    private $buttonurl;
    private $footertext;

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed $contenttop
     */
    public function setContenttop($contenttop): void
    {
        $this->contenttop = $contenttop;
    }

    /**
     * @param mixed $contentbottom
     */
    public function setContentbottom($contentbottom): void
    {
        $this->contentbottom = $contentbottom;
    }

    /**
     * @param mixed $buttonvalue
     */
    public function setButtonvalue($buttonvalue): void
    {
        $this->buttonvalue = $buttonvalue;
    }

    /**
     * @param mixed $buttonurl
     */
    public function setButtonurl($buttonurl): void
    {
        $this->buttonurl = $buttonurl;
    }

    /**
     * @param mixed $footertext
     */
    public function setFootertext($footertext): void
    {
        $this->footertext = $footertext;
    }

    /**
     * @param bool $includebutton
     * @return array|false|string|string[]
     */
    public function renderMail($includebutton = false) {
        if($this->title == NULl) {
            return false;
        }
        if($this->contenttop == NULl) {
            return false;
        }
        if($this->buttonvalue == NULl) {
            return false;
        }
        if($this->buttonurl == NULl) {
            return false;
        }
        if($this->footertext == NULl) {
            return false;
        }
        if($includebutton == true) {
            $template = file_get_contents(__dir__."/../Templates/email-button.tmpl");

        } else {
            $template = file_get_contents(__dir__."/../Templates/email-nobutton.tmpl");
        }
        if($template == false) {
            return false;
        }
        $template = str_replace('VAR_TITLE', $this->title, $template);
        $template = str_replace('VAR_CONTENTSTOP', $this->contenttop, $template);
        $template = str_replace('VAR_CONTENTSBOTTOM', $this->contentbottom, $template);
        if($includebutton == true) {
            $template = str_replace('VAR_BUTTONTEXT', $this->buttonvalue, $template);
            $template = str_replace('VAR_BUTTONURL', $this->buttonurl, $template);
        }

        $template = str_replace('VAR_FOOTERTEXT', $this->footertext, $template);
        return $template;
    }

}
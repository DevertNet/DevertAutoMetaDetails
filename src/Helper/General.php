<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Helper;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;

class General
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var StringTemplateRenderer
     */
    private $templateRenderer;

    public function __construct(SystemConfigService $systemConfigService, StringTemplateRenderer $templateRenderer)
    {
        $this->systemConfigService = $systemConfigService;
        $this->templateRenderer = $templateRenderer;
    }

    public function getSystemConfig(string $path)
    {
        return $this->systemConfigService->get($path);
    }

    public function getTitlePhrases(string $entity, $page)
    {
        //get raw config content
        $raw = $this->systemConfigService->get('DevertAutoMetaDetails.config.' . $entity . 'TitlePhrases');

        //debug
        //$raw = "de-de:aaaaaaDEaaaaaaaa\nbbbGENERALbbbbbbbb\ncccGENEALcccc\n\nen-GB:aaaaENaaaaa\r\nde_DE:bbbbDEDEbbbbbbbb\nde_DE:xxxDEDEbbbbbbbb\nen-GB:bbsbsbsbENsbsbsbs\n";
        //$raw = '     ';
        //$raw = 'de-de:OneLine';

        return $this->_convertRawPhraseToArray($raw, $page);
    }

    public function getDescriptionPhrases(string $entity, $page)
    {
        //get raw config content
        $raw = $this->systemConfigService->get('DevertAutoMetaDetails.config.' . $entity . 'DescriptionPhrases');

        //debug
        //$raw = "de-de:aaaaaaDEaaaaaaaa\nbbbGENERALbbbbbbbb\ncccGENEALcccc\n\nen-GB:aaaaENaaaaa\r\nde_DE:bbbbDEDEbbbbbbbb\nde_DE:xxxDEDEbbbbbbbb\nen-GB:bbsbsbsbENsbsbsbs\n";
        //$raw = '     ';
        //$raw = 'de-de:OneLine';

        return $this->_convertRawPhraseToArray($raw, $page);
    }

    public function _convertRawPhraseToArray($raw, $page)
    {
        $raw = trim((string) $raw);

        //page lang
        $current_lang = $page->getHeader()->getActiveLanguage()->getTranslationCode()->getCode();
        $current_lang = strtolower($current_lang);

        //no content
        if(!$raw)
        {
            return array();
        }

        //convert to array
        $array = explode("\n", $raw);

        //remove empty lines
        $array = array_filter($array);

        //get phrases for current lang
        $out = array();
        foreach($array as $line)
        {
            preg_match('/([a-zA-Z]{2}[-_][a-zA-Z]{2}):(.*)/', $line, $matches);

            if($matches && $matches[1] && $matches[2])
            {
                //lang specific line
                $line_lang = strtolower($matches[1]);
                $line_lang = str_replace('_', '-', $line_lang);

                if($current_lang==$line_lang)
                {
                    $out[] = $matches[2];
                }
            }else
            {
                //general line
                $out[] = $line;
            }
        }

        return $out;
    }

    public function getPhrase(array $phrases, int $increment_id)
    {
        $phrases = array_values($phrases);

        if ($phrases)
        {
            $index = $increment_id % count($phrases);

            if (isset($phrases[$index]) && $phrases[$index])
            {
                return $phrases[$index];
            }
        }

        return false;
    }

    public function getTemplateRenderer()
    {
        return $this->templateRenderer;
    }
}
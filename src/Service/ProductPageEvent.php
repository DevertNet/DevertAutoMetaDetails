<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Service;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Devert\AutoMetaDetails\Helper\General;

class ProductPageEvent implements EventSubscriberInterface
{
    /**
     * @var General
     */
    public $helper;

    /**
     * @var string
     */
    public $entity_name = 'product';

    public function __construct(General $helper)
    {
        $this->helper = $helper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Storefront\Page\Product\ProductPageLoadedEvent' => 'onProductsLoaded'
        ];
    }

    public function onProductsLoaded(ProductPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $context = $event->getContext();
        $sales_channel_context = $event->getSalesChannelContext();

        //check if active
        if(!$this->helper->getSystemConfig('DevertAutoMetaDetails.config.active'))
        {
            return;
        }

        //change meta title
        $this->setMetaTitle($page, $context, $sales_channel_context);

        //change meta description
        $this->setMetaDescription($page, $context, $sales_channel_context);

        //bug fix for empty description (e.g. sw6.1.3)
        $metaInformation = $page->getMetaInformation();
        if(!$metaInformation->getMetaTitle())
        {
            $metaInformation->setMetaTitle((string) $page->getProduct()->getTranslation('name'));
        }
        if(!$metaInformation->getMetaDescription())
        {
            $metaInformation->setMetaDescription((string) $page->getProduct()->getTranslation('description'));
        }
        
    }

    public function setMetaTitle($page, $context, $sales_channel_context)
    {
        $metaInformation = $page->getMetaInformation();
        
        //if product has custom meta title use it and allowed
        if ((string) $page->getProduct()->getMetaTitle() !== '' && $this->helper->getSystemConfig('DevertAutoMetaDetails.config.' . $this->entity_name . 'AllowIndividualMetaData')) {
            $metaInformation->setMetaTitle((string) $page->getProduct()->getMetaTitle());
            return;
        }

        //get phrases from config
        $phrases = $this->helper->getTitlePhrases($this->entity_name, $page);
        //$phrases = array('xx {{ "detail.addProduct"|trans|sw_sanitize }} aaas {{ name|slice(0, 10) }} {{ page.product.productnumber }} {{ context.salesChannel.name }} dasdasdasdasd');

        //check if there are phrases
        if(!$phrases)
        {
            return;
        }

        //get phrase for this product id
        $new_meta_title = $this->helper->getPhrase($phrases, $page->getProduct()->getAutoIncrement());
        
        //render meta title
        //$this->templateRenderer->enableTestMode();
        $output = $this->helper->getTemplateRenderer()->render($new_meta_title, $this->getTemplateVariables($page, $sales_channel_context, $context), $context);

        //set meta title
        $metaInformation->setMetaTitle($output);
    }

    public function setMetaDescription($page, $context, $sales_channel_context)
    {
        $metaInformation = $page->getMetaInformation();
        
        //if product has custom meta title use it and allowed
        if ((string) $page->getProduct()->getMetaDescription() !== '' && $this->helper->getSystemConfig('DevertAutoMetaDetails.config.' . $this->entity_name . 'AllowIndividualMetaData')) {
            $metaInformation->setMetaDescription((string) $page->getProduct()->getMetaDescription());
            return;
        }

        //get phrases from config
        $phrases = $this->helper->getDescriptionPhrases($this->entity_name, $page);
        //$phrases = array('aaas {{ name|slice(0, 10) }} {{ page.product.productnumber }} {{ context.salesChannel.name }} dasdasdasdasd');

        //check if there are phrases
        if(!$phrases)
        {
            return;
        }

        //get phrase for this product id
        $new_meta_description = $this->helper->getPhrase($phrases, $page->getProduct()->getAutoIncrement());
        
        //render meta title
        //$this->templateRenderer->enableTestMode();
        $output = $this->helper->getTemplateRenderer()->render($new_meta_description, $this->getTemplateVariables($page, $sales_channel_context, $context), $context);

        //set meta title
        if($output)
        {
            $metaInformation->setMetaDescription($output);
        }
    }

    public function getTemplateVariables($page, $sales_channel_context, $context)
    {
        return array(
            'name' => $page->getProduct()->getTranslation('name'),
            'description' => $page->getProduct()->getTranslation('description'),
            'price' => $this->renderPrice($page, $sales_channel_context, $context),
            'page' => $page,
            'context' => $sales_channel_context,
        );
    }

    public function renderPrice($page, $sales_channel_context, $context)
    {
        $price_template = '{% set product = page.product %}
        {% set purchaseUnit = product.purchaseUnit %}
        {% set listingPrice = product.calculatedListingPrice %}
        {% set fromPrice = listingPrice.from %}
        {% set referncePrice = product.calculatedPrice.referencePrice %}
        {% set listPrice = product.priceRange or product.calculatedPrices.count > 0 ? null : product.calculatedPrice.listPrice %}
    
        {% if product.priceRange %}
            {{ fromPrice.unitPrice|currency }}
            -
            {{ listingPrice.to.unitPrice|currency }}
        {% elseif product.calculatedPrices|length == 1 %}
            {{ product.calculatedPrices.first.unitPrice|currency }}
        {% else %}
            {{ product.calculatedPrice.unitPrice|currency }}

            {% if listPrice.percentage > 0 %}
                    {{ listPrice.price|currency }}
            {% endif %}
        {% endif %}';
        return $this->helper->getTemplateRenderer()->render($price_template, array('page' => $page, 'context' => $sales_channel_context), $context);
    }
}
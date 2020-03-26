<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Service;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Loader\ArrayLoader;
use Devert\AutoMetaDetails\Helper\General;

class ProductPageEvent implements EventSubscriberInterface
{
    public $twig;
    public $helper;

    public function __construct($twig, General $helper)
    {
        $this->twig = $twig;
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

        //change meta title
        $this->setMetaTitle($page, $context);
    }

    public function setMetaTitle($page, $context)
    {
        $meta_information = $page->getMetaInformation();

        //if product has custom meta title use it
        if ((string) $page->getProduct()->getMetaTitle() !== '') {
            $metaInformation->setMetaTitle((string) $page->getProduct()->getMetaTitle());
            return;
        }

        //https://docs.shopware.com/en/shopware-platform-dev-en/how-to/reading-plugin-config
        $phrases = array(
            'aaas {{ name|slice(0, 10) }} {{ page.product.productnumber }} dasdasdasdasd'
        );

        //get phrase for this product id
        $new_meta_title = $this->helper->getPhrase($phrases, $page->getProduct()->getAutoIncrement());
        
        //options
        $vars = array(
            'name' => $page->getProduct()->getTranslation('name'),
            'page' => $page
        );

        //render meta title
        $twig = $this->twig;
        $originalLoader = $twig->getLoader();
        $twig->setLoader(new ArrayLoader([
            'product_meta_title_tmp.html.twig' => $new_meta_title,
        ]));
        $output = $twig->render('product_meta_title_tmp.html.twig', $vars);

        //set meta title
        $meta_information->setMetaTitle($output);
        var_dump($output);
        die;
    }
}
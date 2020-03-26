<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Service;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Loader\ArrayLoader;

class ProductPageEvent implements EventSubscriberInterface
{
    public $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
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

        $new_meta_title = 'aaas {{ name|slice(0, 10) }} {{ page.product.productnumber }} dasdasdasdasd';
        
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
<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Service;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Devert\AutoMetaDetails\Helper\General;

class CategoryPageEvent implements EventSubscriberInterface
{
    /**
     * @var General
     */
    public $helper;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var string
     */
    public $entity_name = 'category';

    public function __construct(General $helper, SalesChannelRepositoryInterface $categoryRepository)
    {
        $this->helper = $helper;
        $this->categoryRepository = $categoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent' => 'onCategoryLoaded'
        ];
    }

    public function onCategoryLoaded(NavigationPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $context = $event->getContext();
        $request = $event->getRequest();
        $sales_channel_context = $event->getSalesChannelContext();

        //check if active
        if(!$this->helper->getSystemConfig('DevertAutoMetaDetails.config.active'))
        {
            return;
        }

        //load category, because it not passed over the event
        $navigationId = $request->get('navigationId', $sales_channel_context->getSalesChannel()->getNavigationCategoryId());
        $category = $this->loadCategory($navigationId, $sales_channel_context);

        //change meta title
        $this->setMetaTitle($page, $context, $sales_channel_context, $request, $category);

        //change meta description
        $this->setMetaDescription($page, $context, $sales_channel_context, $request, $category);

        //bug fix for empty description (e.g. sw6.1.3)
        $metaInformation = $page->getMetaInformation();
        if(!$metaInformation->getMetaTitle())
        {
            $metaInformation->setMetaTitle((string) $category->getTranslation('name'));
        }
        if(!$metaInformation->getMetaDescription())
        {
            $metaInformation->setMetaDescription((string) $category->getTranslation('description'));
        }
    }

    public function setMetaTitle(NavigationPage $page, $context, SalesChannelContext $sales_channel_context, $request, $category)
    {
        $metaInformation = $page->getMetaInformation();
        
        //if category has custom meta title use it and allowed
        if ((string) $category->getMetaTitle() !== '' && $this->helper->getSystemConfig('DevertAutoMetaDetails.config.' . $this->entity_name . 'AllowIndividualMetaData')) {
            $metaInformation->setMetaTitle((string) $category->getMetaTitle());
            return;
        }

        //var_dump($category);
        //die;

        //get phrases from config
        $phrases = $this->helper->getTitlePhrases($this->entity_name, $page);
        //$phrases = array('aaas PPPPP {{ category.translated.name|slice(0, 20)|raw }} {{ context.salesChannel.name }} dasdasdasdasd');

        //check if there are phrases
        if(!$phrases)
        {
            return;
        }

        //get phrase for this id
        $new_meta_title = $this->helper->getPhrase($phrases, $category->getAutoIncrement());
        
        //render meta title
        //$this->templateRenderer->enableTestMode();
        $output = $this->helper->getTemplateRenderer()->render($new_meta_title, $this->getTemplateVariables($page, $sales_channel_context, $category), $context);

        //set meta title
        $metaInformation->setMetaTitle($output);
    }

    public function setMetaDescription(NavigationPage $page, $context, SalesChannelContext $sales_channel_context, $request, $category)
    {
        $metaInformation = $page->getMetaInformation();
        
        //if category has custom meta title use it and allowed
        if ((string) $category->getMetaDescription() !== '' && $this->helper->getSystemConfig('DevertAutoMetaDetails.config.' . $this->entity_name . 'AllowIndividualMetaData')) {
            $metaInformation->setMetaDescription((string) $category->getMetaDescription());
            return;
        }

        //get phrases from config
        $phrases = $this->helper->getDescriptionPhrases($this->entity_name, $page);

        //check if there are phrases
        if(!$phrases)
        {
            return;
        }

        //get phrase for this id
        $new_meta_description = $this->helper->getPhrase($phrases, $category->getAutoIncrement());
        
        //render meta title
        //$this->templateRenderer->enableTestMode();
        $output = $this->helper->getTemplateRenderer()->render($new_meta_description, $this->getTemplateVariables($page, $sales_channel_context, $category), $context);

        //set meta title
        if($output)
        {
            $metaInformation->setMetaDescription($output);
        }
    }

    public function getTemplateVariables($page, $sales_channel_context, $category)
    {
        return array(
            'name' => $category->getTranslation('name'),
            'description' => $category->getTranslation('description'),
            'category' => $category,
            'page' => $page,
            'context' => $sales_channel_context,
        );
    }

    public function loadCategory(string $categoryId, SalesChannelContext $context): CategoryEntity
    {
        $criteria = new Criteria([$categoryId]);
        $criteria->addAssociation('media');

        $category = $this->categoryRepository->search($criteria, $context)->get($categoryId);

        if (!$category) {
            throw new CategoryNotFoundException($categoryId);
        }

        return $category;
    }
}
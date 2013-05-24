<?php

namespace Msi\CmfBundle\Twig\Extension;

class CmfExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'msi_is_image' => new \Twig_Function_Method($this, 'isImage', array('is_safe' => array('html'))),
            'trans_date' => new \Twig_Function_Method($this, 'transDate', array('is_safe' => array('html'))),
            'msi_block_render' => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
            'msi_pager_render' => new \Twig_Function_Method($this, 'renderPaginator', array('is_safe' => array('html'))),
        );
    }

    public function getGlobals()
    {
        $globals = [];

        $globals['app_locales'] = $this->container->getParameter('msi_cmf.app_locales');

        if (!$this->container->isScopeActive('request')) {
            return $globals;
        }

        $request = $this->container->get('request');

        $site = $this->container->get('msi_cmf.provider')->getSite();
        $globals['site'] = $site;

        // set page
        $page = $this->container->get('msi_cmf.page_manager')->findByRoute($request->attributes->get('_route'));
        if (!$page) {
            $page = $this->container->get('msi_cmf.page_manager')->findOneOrCreate($this->container->getParameter('msi_cmf.app_locales'));
        }
        $globals['page'] = $page;

        return $globals;
    }

    public function transDate($date, $format = 'd F')
    {
        return preg_replace([
            '#January#',
            '#February#',
            '#March#',
            '#April#',
            '#May#',
            '#June#',
            '#July#',
            '#August#',
            '#September#',
            '#October#',
            '#November#',
            '#December#',
        ], [
            'janvier',
            'février',
            'mars',
            'avril',
            'mai',
            'juin',
            'juillet',
            'août',
            'septembre',
            'octobre',
            'novembre',
            'décembre',
        ], date($format, strtotime($date)));
    }

    public function isImage($pathname)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$pathname)) {
            return false;
        }

        $handle = @getimagesize($_SERVER['DOCUMENT_ROOT'].$pathname);

        return $handle ? true : false;
    }

    public function renderBlock($slot, $page)
    {
        $content = '';
        foreach ($page->getBlocks() as $block) {
            if ($block->getRendered() === true) {
                continue;
            }
            if (!$block->getTranslation()->getPublished()) {
                continue;
            }
            if ($block->getSlot() !== $slot) {
                continue;
            }
            $handler = $this->container->get($block->getType());
            $content .= $handler->execute($block, $page);
            $block->setRendered(true);
        }

        return $content;
    }

    public function renderPaginator($paginator, array $options = [])
    {
        $numPages = $paginator->countPages();
        $options = array_merge($paginator->getOptions(), $options);

        if ($numPages < 2) return;

        $pagination = array();
        // previous
        if ($paginator->getPage() != 1) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() - 1), 'label' => '«');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl(1), 'label' => '«');
        }
        // first
        if ($paginator->getPage() > 4) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl(1), 'label' => 1);
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
        }
        // middle
        if ($numPages > 1) {
            for ($i=$paginator->getPage() - 4; $i < $paginator->getPage() - 4 + 7; $i++) {
                if ($i + 1 == $paginator->getPage()) {
                    $pagination[] = array('attr' => array('class' => 'active'), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                } else if ($i >= 0 && $i <= $numPages - 1) {
                    $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                }
            }
        }
        // last
        if ($paginator->getPage() < $numPages - 3) {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($numPages), 'label' => $numPages);
        }
        // next
        if ($paginator->getPage() != $numPages) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() + 1), 'label' => '»');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl($numPages), 'label' => '»');
        }

        if ($paginator->getPage() > $numPages) return;

        return $this->container->get('templating')->render('MsiCmfBundle:Pager:'.$options['template'].'.html.twig', array('paginator' => $paginator, 'pagination' => $pagination));
    }

    protected function generateUrl($page)
    {
        $request = $this->container->get('request');

        $parameters = array_merge($request->query->all(), array('page' => $page));

        return $this->container->get('router')->generate($request->attributes->get('_route'), $parameters);
    }

    public function getName()
    {
        return 'msi_cmf';
    }
}

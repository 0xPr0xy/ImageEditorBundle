<?php

namespace PyDev\ImageEditorBundle\Controller;

use Kunstmaan\PagePartBundle\Repository\PagePartRefRepository;
use PyDev\ImageEditorBundle\Entity\EditableImagePagePart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Kunstmaan\MediaBundle\Entity\Media;

const DEFAULT_FILTER = 'runtime_filter';

/*
 * Documentation:
 * http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html
 * https://github.com/liip/LiipImagineBundle/tree/master/Resources/doc
 *
 */

class EditController extends Controller
{
    /**
     * @Route("/thumbnail/{width},{height},{mode}/{fileName}/{pagePartId}", name="thumbnail")
     * @param $width
     * @param $height
     * @param $mode
     * @param $fileName
     * @param int $pagePartId
     * @param Request $request
     * @return RedirectResponse
     */
    public function thumbnailAction($width, $height, $mode, $fileName, $pagePartId, Request $request)
    {
        $this->setThumbnailFilterConfig($width, $height, $mode);
        $image = $this->getRelativePathForFile($fileName);
        $redirectResponse = $this->applyFilterToImage($request, $image, $pagePartId);
        return $redirectResponse;
    }

    /**
     * @Route("/resize/{mode},{number}/{fileName}/{pagePartId}", name="resize")
     * @param $mode
     * @param $number
     * @param $fileName
     * @param int $pagePartId
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function resizeAction($mode, $number, $fileName, $pagePartId, Request $request)
    {
        $this->setResizeFilterConfig($number, $mode);
        $image = $this->getRelativePathForFile($fileName);
        $redirectResponse = $this->applyFilterToImage($request, $image, $pagePartId);
        return $redirectResponse;
    }

    /**
     * @Route("/crop/{x},{y}/{width},{height}/{fileName}/{pagePartId}", name="crop")
     * @param $x
     * @param $y
     * @param $width
     * @param $height
     * @param $fileName
     * @param Request $request
     * @param int $pagePartId
     * @internal param $mode
     * @internal param $number
     * @return RedirectResponse
     */
    public function cropAction($x, $y, $width, $height, $fileName, $pagePartId, Request $request)
    {
        $this->setCropFilterConfig($x, $y, $width, $height);
        $image = $this->getRelativePathForFile($fileName);
        $redirectResponse = $this->applyFilterToImage($request, $image, $pagePartId);
        return $redirectResponse;
    }

    /**
     * @param Request $request
     * @param $image
     * @param int $pagePartId
     * @return Response
     */
    private function applyFilterToImage(Request $request, $image, $pagePartId)
    {
        $imagineController = $this->container->get('liip_imagine.controller');
        $imagineController->filterAction($request, $image, DEFAULT_FILTER);

        $imagePath = 'uploads/cache/' . DEFAULT_FILTER .'/'. $image;
        $mediaCreatorService = $this->container->get('kunstmaan_media.media_creator_service');
        $media = $mediaCreatorService->createFile($imagePath, 1);

        $em = $this->getDoctrine()->getManager();

        /* @var PagePartRefRepository $repo */
        $repo = $em->getRepository('KunstmaanPagePartBundle:PagePartRef');

        $pagePartRef = $repo->find($pagePartId);
        $entityRepo = $em->getRepository($pagePartRef->getPagePartEntityName());

        /* @var EditableImagePagePart $pagePart */
        $pagePart = $entityRepo->find($pagePartRef->getPagePartId());
        $pagePart->setMedia($media);
        $em->persist($pagePart);
        $em->flush();

        return new Response(200);
    }

    /**
     * @param $width
     * @param $height
     * @param $mode
     *
     * @internal param $filterType
     */
    private function setThumbnailFilterConfig($width, $height, $mode)
    {
        $filterManager = $this->container->get('liip_imagine.filter.manager');
        $filterConfig = $filterManager->getFilterConfiguration();
        $config['filters']['thumbnail']['size'] = array($width, $height);
        $config['filters']['thumbnail']['mode'] = $mode;
        $filterConfig->set(DEFAULT_FILTER, $config);
    }

    /**
     * @param $number
     * @param $mode
     *
     * @internal param $filterType
     * @internal param $width
     * @internal param $height
     */
    private function setResizeFilterConfig($number, $mode)
    {
        $filterManager = $this->container->get('liip_imagine.filter.manager');
        $filterConfig = $filterManager->getFilterConfiguration();
        $config['filters']['relative_resize'][$mode] = $number;
        $filterConfig->set(DEFAULT_FILTER, $config);
    }

    /**
     * @param $x
     * @param $y
     * @param $width
     * @param $height
     *
     * @internal param $number
     * @internal param $mode
     *
     * @internal param $filterType
     * @internal param $width
     * @internal param $height
     */
    private function setCropFilterConfig($x, $y, $width, $height)
    {
        $filterManager = $this->container->get('liip_imagine.filter.manager');
        $filterConfig = $filterManager->getFilterConfiguration();
        $config['filters']['crop']['start'] = array($x, $y);
        $config['filters']['crop']['size'] = array($width, $height);
        $filterConfig->set(DEFAULT_FILTER, $config);
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    private function getRelativePathForFile($fileName)
    {
        return 'uploads/media/' . $fileName;
    }
}

<?php

namespace Acme\BlogBundle\Controller;

use Acme\BlogBundle\Exception\InvalidFormException;
use Acme\BlogBundle\Form\PageType;
use Acme\BlogBundle\Model\PageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends FOSRestController
{
    /**
     * List all pages.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing pages.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many pages to return.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getPagesAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $offset = (null == $offset) ? 0 : $offset;
        $limit = $paramFetcher->get('limit');
        $pages = $this->container->get('acme_blog.page.handler')->all($limit, $offset);

//        var_dump($pages);die;

        return ['pages' =>$this->container->get('acme_blog.page.handler')->all($limit, $offset)];
    }

    /**
     * Get single Page.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets a Page for a given id",
     *   output = "Acme\BlogBundle\Entity\Page",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the page is not found"
     *   }
     * )
     *
     * @Annotations\View(templateVar="page")
     *
     * @param int     $id      the page id
     *
     * @return array
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function getPageAction($id)
    {
        $page = $this->getOr404($id);

        return $page;
    }

    /**
     * Presents the form to use to create a new page.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View(
     *  templateVar = "form"
     * )
     *
     * @return FormTypeInterface
     */
    public function newPageAction()
    {
        return $this->createForm(PageType::class);
    }

    /**
     * Create a Page from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new page from the submitted data.",
     *   input = "Acme\BlogBundle\Form\PageType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "AcmeBlogBundle:Page:newPage.html.twig",
     *  statusCode = Response::HTTP_BAD_REQUEST,
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|View
     */
    public function postPageAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $newPage = $this->container->get('acme_blog.page.handler')->post(
                $request->request->all()
            );

            $routeOptions = array(
                'id' => $newPage->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('api_1_get_page', $routeOptions, Response::HTTP_CREATED);

        } catch (InvalidFormException $exception) {

            return $exception->getForm();
        }
    }
    /*
     {
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ1c2VybmFtZSI6ImJvYiIsInJvbGUiOlsiUk9MRV9VU0VSIl0sImlhdCI6IjE0NzExODA1ODIifQ.aB4kz_HIvIXvSW9TYYiE68ZW4FAwnKgoocUO1X8z1WFfPvszXpmZI_JJV8mZmVs7W90xsvkFs1DgzWbUUpTRsW2aHYRK2k2LOPbmX051q5_zeooqEEyueyYML4jKSkC4XhR5tdp5YCNVKD15OAvupANfX__obfDPYsCWiRxfprIYwjPZWapA53ZAr_B8lWqcxJ02Ok0alUL8SVV88kdD6xCPEQBzvyTKPoqfWabG2i8JSKzsBY1pnY-O-qzabD7lkb7JCO179V-a0gLxRrvYqED3fPI1RQ_55uyYoJjuDp_J3cyqJ8eTD2BwBVUHHFiKu10Hyan4WazYdtLKEC1GcqX_KPol6SMbGZ6PI-Bs1yOg69GYfMbs1qo9ZhKFdC-VQnUkHK8uW-24wU31eU4fWH4aAR37yaYEuC87YuVuHASubDR95tcENsmX4ykxq_EcIN9FvRtcXuOUO_FNeSqfS7rQyyLMch8riZ1vYvam7G3FhLVDGf1nbJZgy-fUHJyh-UpbxRW8qJzSR3-a8J_BMQ8aOArvg-MaaXUhwFWzngkKaQ61JOzUgKKBPRBHMhDwieV4DfDDYAvJA82wlOLxe6PVBGg7Svqy8Ah6hzfFytiwz-VSR8HVfQ3bmjZiH1aT5mLY1r3cwsZRK1c1NkxC7mwvQw5pSVx8NSilFfg4ew4"
}
     */

    /**
     * Update existing page from the submitted data or create a new page at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Acme\DemoBundle\Form\PageType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "AcmeBlogBundle:Page:editPage.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function putPageAction(Request $request, $id)
    {
        try {
            if (!($page = $this->container->get('acme_blog.page.handler')->get($id))) {
                $statusCode = Response::HTTP_CREATED;
                $page = $this->container->get('acme_blog.page.handler')->post(
                    $request->request->all()
                );
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
                $page = $this->container->get('acme_blog.page.handler')->put(
                    $page,
                    $request->request->all()
                );
            }

            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('api_1_get_page', $routeOptions, $statusCode);

        } catch (InvalidFormException $exception) {

            return $exception->getForm();
        }
    }

    /**
     * Update existing page from the submitted data or create a new page at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Acme\DemoBundle\Form\PageType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "AcmeBlogBundle:Page:editPage.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function patchPageAction(Request $request, $id)
    {
        try {
            $page = $this->container->get('acme_blog.page.handler')->patch(
                $this->getOr404($id),
                $request->request->all()
            );

            $routeOptions = array(
                'id' => $page->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('api_1_get_page', $routeOptions, Response::HTTP_NO_CONTENT);

        } catch (InvalidFormException $exception) {

            return $exception->getForm();
        }
    }

    /**
     * Fetch a Page or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return PageInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($page = $this->container->get('acme_blog.page.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$id));
        }

        return $page;
    }
}

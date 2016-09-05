<?php

namespace Acme\BlogBundle\Handler;

use Acme\BlogBundle\Model\PageInterface;

interface PageHandlerInterface
{
    /**
     * Get a Page given the identifier
     *
     * @api
     *
     * @param mixed $catId
     * @param mixed $pageId
     *
     * @return PageInterface
     */
    public function get($catId, $pageId);

    /**
     * Get a list of Pages.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     * @param int $cat_id category id
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0, $cat_id);

    /**
     * Post Page, creates a new Page.
     *
     * @api
     *
     * @param array $parameters
     *
     * @return PageInterface
     */
    public function post(array $parameters);

    /**
     * Edit a Page.
     *
     * @api
     *
     * @param PageInterface   $page
     * @param array           $parameters
     *
     * @return PageInterface
     */
    public function put(PageInterface $page, array $parameters);

    /**
     * Partially update a Page.
     *
     * @api
     *
     * @param PageInterface   $page
     * @param array           $parameters
     *
     * @return PageInterface
     */
    public function patch(PageInterface $page, array $parameters);
}
<?php

namespace Acme\BlogBundle\Repository;

/**
 * PageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PageRepository extends BaseRepository
{
    /**
     * @param array $params
     *
     * @return $this
     */
    public function buildQuery($params = array())
    {
        parent::buildQuery($params);

//        $this->query
//            ->select(array('tbl'));

        if (!empty($params['withUserId'])) {
            $this->query
                ->addSelect('u.id')
                ->join('tbl.postedBy', 'u');
        }

        return $this;
    }
}

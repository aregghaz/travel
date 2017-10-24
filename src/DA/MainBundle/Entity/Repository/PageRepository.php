<?php

namespace DA\MainBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * PageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PageRepository extends \Doctrine\ORM\EntityRepository
{
    public function getPageBySlug($slug)
    {

        $query = $this->getEntityManager()
            ->createQuery('SELECT p FROM DAMainBundle:Page p
                            WHERE p.slug = :slug
                            ')
            ->setParameter('slug',$slug);
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result[0];
    }
    public function getComfortByObject($object)
    {

        $category = array(
            1 => 'bathroom',
            2 => 'bedroom',
            3 => 'media_and_technology',
            4 => 'internet',
            5 => 'reception',
            6 => 'overall',
        );

        $query = $this->getEntityManager()
            ->createQuery('SELECT com.category FROM DAMainBundle:Comfort com
                            LEFT JOIN com.'.$object.'  o
                            group by com.category ORDER BY com.category 
                            ')
        ;
        $result = $query->getResult();
        $keys = array();
        foreach ($result as $item){
            $keys[$item['category']] = $category[$item['category']];
        }



        $query = $this->getEntityManager()
            ->createQuery('SELECT com FROM DAMainBundle:Comfort com
                            LEFT JOIN com.'.$object.'  o
                            ORDER BY com.category
                            ')
            ;
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();

        if(!$result){
            return null;
        }
        return array('result'=>$result,'keys'=>$keys);
    }

}
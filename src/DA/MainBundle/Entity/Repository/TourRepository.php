<?php

namespace DA\MainBundle\Entity\Repository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * TourRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TourRepository extends \Doctrine\ORM\EntityRepository
{

    public function getTourInCategory($id)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT t,RAND() as HIDDEN rand FROM DAMainBundle:TourName t
                            WHERE t.category = :id
                            ORDER BY  rand
                            ')
        ->setParameter('id',$id)
        ->setMaxResults(3)
        ;
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }

    public function getTourBySlug($id)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT t FROM DAMainBundle:Tour t
                          
                            WHERE t.id = :id 
                            ')
        ->setParameter('id',$id)
        ;
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result[0];
    }
    public function getBestTours()
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT t FROM DAMainBundle:Tour t
                          
                            WHERE t.best_tour =  TRUE 
                            ')
             ->setMaxResults(4);
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }

    public function getAllTours()
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT t,tt FROM DAMainBundle:TourName t
                            LEFT JOIN t.tour tt ORDER BY t.price
                            ');
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }
    public function getAllToursCategory()
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT cat.name, cat.title FROM DAMainBundle:TourName t
                            LEFT JOIN t.category cat
                            WHERE cat.name is NOT  NULL
                             GROUP BY cat.name
                            ');
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }
    public function getCategory()
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT cat FROM DAMainBundle:TourCategory cat
                            ');
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }

    public function getToursCity()
    {

        $query = $this->getEntityManager()
            ->createQuery('SELECT l FROM DAMainBundle:Location l
                            JOIN l.tour t
                            ')
           ;
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $result = $query->getResult();
        if(!$result){
            return null;
        }
        return $result;
    }

}
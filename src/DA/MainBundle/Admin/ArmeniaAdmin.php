<?php

namespace DA\MainBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
//use Travel\MainBundle\Entity\Seo;

class ArmeniaAdmin extends AbstractAdmin
{

    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'ASC', // sort direction
        '_sort_by' => 'id' // field name
    );

    /**
     * Row show configuration
     *
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     * @return void
     */
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, array('label' => 'ID'))
            ->add('name', null, array('label' => 'Title'));
    }

    /**
     * List show configuration
     *
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array('label' => 'Name'))
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array(),
                'delete' => array()
            )));
    }

    /**
     * Row form edit configuration
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get all languages
        $languages = $this->configurationPool->getContainer()->getParameter('languages');
        $formMapper
            ->with('General', array(
                'class'       => 'col-md-12',
                'box_class'   => 'box box-solid box-discover',
                // ...
            ))
            ->add('name',null)
            ->add('translations', 'a2lix_translations_gedmo', array(
                'translatable_class' => 'DA\MainBundle\Entity\Armenia',
                'by_reference' => false,
                'label' => false,
                'locales' => array_keys($languages),
                'fields'=>array(
                    'title'=>array(
                        'field_type'=>'text',
                        'required' => false,
                    ),
                    'banner_title'=>array(
                        'field_type'=>'text',
                        'required' => false,
                    ),
                    'banner_description'=>array(
                        'field_type'=>'textarea',
                        'required' => false,
                    )
                )
            ))
            ->add('banner_image', 'sonata_type_model_list', array('required' => false))
        ->end()
        ->with('Blocks', array(
            'class'       => 'col-md-12',
            'box_class'   => 'box box-solid box-discover armenia_block_admin',
            // ...
        ))
            ->add('block', 'sonata_type_collection', array(
                'label' => 'Add Block',
                'required' => false,
                'by_reference' => false,
                'type_options' => array('delete' => true)

            ), array(
                'data_class' => 'DA\MainBundle\Entity\ArmaniaBlock',
                'admin_code' => 'armenia.block.admin',
                'edit' => 'inline',
                //'inline' => 'table',

            ))
        ->end();
    }

    /**
     * Fields in list rows search
     *
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name');
    }

}
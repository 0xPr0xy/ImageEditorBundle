<?php

namespace PyDev\ImageEditorBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Kunstmaan\MediaBundle\Form\Type\CurrentValueContainer;
use Kunstmaan\MediaBundle\Form\Type\IdToMediaTransformer;
use Kunstmaan\MediaBundle\Helper\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * EditImageType
 */
class EditImageType extends AbstractType
{

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param MediaManager  $mediaManager  The media manager
     * @param ObjectManager $objectManager The media manager
     */
    public function __construct($mediaManager, $objectManager)
    {
        $this->mediaManager  = $mediaManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new IdToMediaTransformer($this->objectManager, $options['current_value_container']),
            true
        );
        $builder->setAttribute('chooser', $options['chooser']);
        $builder->setAttribute('mediatype', $options['mediatype']);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
                'compound'                => false,
                'chooser'                 => 'KunstmaanMediaBundle_chooser',
                'mediatype'               => null,
                'current_value_container' => new CurrentValueContainer(),
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'edit_image';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['chooser']      = $form->getConfig()->getAttribute('chooser');
        $view->vars['mediatype']    = $form->getConfig()->getAttribute('mediatype');
        $view->vars['mediamanager'] = $this->mediaManager;
    }
}

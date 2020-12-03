<?php

namespace Lch\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlugType extends AbstractType
{
    /**
     * The form name.
     */
    const NAME = 'lch_slug_type';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('slug', TextType::class, array(
                'translation_domain' => 'LchSeoBundle',
                'required' => true,
                'label' => 'lch.seo.form.url.name',
                'block_name' => 'slug',
                'attr' => [
                    'helper' => 'lch.seo.form.description.helper',
                ]
            ))
        ;
    }

    /**
     * @inheritdoc
     * inherit_data : https://symfony.com/doc/2.8/cookbook/form/inherit_data_option.html
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true,
        ));
    }
    
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}

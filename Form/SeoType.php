<?php

namespace Lch\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeoType extends AbstractType
{
    /**
     * The form name.
     */
    const NAME = 'lch_seo_type';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seoTitle', TextType::class, array(
                'label' => 'lch.seo.form.title.name',
                'translation_domain' => 'LchSeoBundle',
                'required' => false,
                'attr' => [
                    'helper' => 'lch.seo.form.title.helper',
                ]
            ))
            ->add('seoDescription', TextareaType::class, array(
                'translation_domain' => 'LchSeoBundle',
                'label' => 'lch.seo.form.description.name',
                'required' => false,
                'attr' => [
                    'helper' => 'lch.seo.form.description.helper',
                ]
            ))
            ->add('slug', TextType::class, array(
                'translation_domain' => 'LchSeoBundle',
                'required' => true,
                'label' => 'lch.seo.form.url.name',
                'block_name' => 'slug',
                'attr' => [
                    'helper' => 'lch.seo.form.url.helper',
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
            'title_max_char' => 60,
            'description_max_char' => 155,
        ));
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, array(
            'title_max_char' => $options['title_max_char'],
            'description_max_char' => $options['description_max_char']
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

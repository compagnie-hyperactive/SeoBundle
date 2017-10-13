# SEOBundle

This bundle add SEO capabilities for entities in any SF3 project. So far it handles
- SEO meta (title, desc, slug with URL rewrite)
- Minimal OpenGraph implementation
- Canonical URL

## Installation
Simply do a `composer require lch/seo-bundle`
## Configuration

1. [Entities preparations](#entities-preparation)
2. [Form type usage](#form-type-usage)

### Entities preparation

2 steps here :
#### Seoable

Add `Seoable` to any entity you want to have SEO settings on. This include
 - seoTitle
 - seoDescription
 - slug field 

#### SeoInterface

Implements `SeoInterface` and fill 5 methods

 1. `getSluggableFields` for getting fields name needed to build slug
```php
    /**
     * @inheritdoc
     */
    public function getSluggableFields() {
        // This assume your entity have a field 'title'
        return [
            'title'
        ];
    }
```

 2. `getRouteFields` for getting fields name needed to build route

 An array is expected here, each key based on following pattern : routeParameter => entityParameter
```php

    /**
     * @inheritdoc
     */
    public function getRouteFields()
    {
        return [
            'slug' => 'slug'
        ];
    }
```

 3. `getRouteName` for allowing URL generation in SEO area (canonical URL, OpenGraph...)
```php
    /**
     * @inheritdoc
     */
    public function getRouteName()
    {
        // This assume to return the entity show page route
        return 'yourproject_yourentity_show';
    }
```
 4. `getSeoTitleDefaultValue` for pointing a field to use in case of SEO title empty (to generate default one)
 ```php
    /**
      * @inheritdoc
      */
     public function getSeoTitleDefaultValue()
     {
         return $this->title;
     }
 ```
 5. `getOpenGraphData` should return an array with OpenGraph data, such as :
 So far, `SeoInterface` declares following OG constants :
 ```php
     const OG_TITLE = 'title';
     const OG_TYPE = 'type';
     const OG_URL = 'url';
     const OG_IMAGE = 'image';
 ```

 Array returned example :
```php
    /**
     * @inheritdoc
     */
    public function getOpenGraphData()
    {
        $openGraphData = [
            static::OG_TITLE => $this->title,
            static::OG_TYPE => "Open Graph type"
        ];

        // Image check example
        if($this->headBandImage instanceof Image) {
            $imageData = explode('/web', $this->getHeadBandImage()->getFile());
            $openGraphData[static::OG_IMAGE] = array_pop($imageData);
        }

        return $openGraphData;
    }
```
 
We assume that a unique constraint/index is set on slug field, or slug fields collection if more than one.
 
### Form type usage

#### SeoType
The bundle provides an SeoType, you can add to entities implementing SeoInterface types

```php
 /**
        * {@inheritdoc}
        */
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('title', TextType::class, [
                    'label' => static::ROOT_TRANSLATION_PATH . '.title.label',
                    'attr'  => [
                        'helper' => static::ROOT_TRANSLATION_PATH . '.title.helper',
                    ]
                ])

                // ...

                ->add('seo', SeoType::class, array(
                    'label' => 'lch.seo.form.label',
                    'required' => false,
                    'attr' => [
                        'no_label' => true,
                        'force_two_columns_presentation' => true
                    ]
                ))
            ;
        }
```
_Note: `attr` used are detailled with [AdminBundle](https://github.com/compagnie-hyperactive/AdminBundle)_

Then, in the form twigs, add SEO form theme : `LchSeoBundle:form:fields.html.twig` to ensure fields rendering and logic.


### Persistence
So far, add call to `$this->get('lch.seo.tools')->seoFilling()` on controller before persist to ensure data will be set. Will be replaced with doctrine event later
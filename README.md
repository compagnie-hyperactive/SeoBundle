# SEOBundle

This bundle add SEO capabilities for entities in any SF3 project. So far it handles:
- SEO meta (title, desc, slug with URL rewrite)
- Minimal OpenGraph implementation
- Canonical URL

## Installation using [composer](https://getcomposer.org/)
`$ composer require lch/seo-bundle`
## Configuration and usage

1. [Configuration](#configuration)
2. [Entities preparations](#entities-preparation)
3. [Form type usage](#form-type-usage)
4. [Front rendering](#front-rendering)

### Configuration

SeoBundle allows to generate minimal SEO requirements for specific pages (like homepage) or 'entity' pages (like news page).
SEO for 'entity' pages is automatically generated. For specifics pages, follow these steps:

```yml
# app/config/config.yml

lch_seo:
    specific:
        route_name:
            tags:
                title: Page title               # Title of the current page
                description: Page description   # Desctiption (meta) of the current page
            sitemap:
                loc: /                          # URL of page
                priority: 1.0                   # Priority
        other_route_name:
            ...
```

### Entities preparation


To automatically generate SEO for 'entity' pages, follow theses 2 steps:
#### Seoable trait

Add `Seoable` to any entity you want to have SEO settings on. This include
 - seoTitle
 - seoDescription
 - slug field 
 
 ```php
     use Lch\SeoBundle\Behaviour\Seoable;
     use Lch\SeoBundle\Model\SeoInterface;
    
     class MyEntity implements SeoInterface
     {
     
         use Seoable;
         
         ...
 ```
 

#### SeoInterface implementation

Implements `SeoInterface` and fill 5 methods

 1. `getSluggableFields` for getting fields name needed to build slug
```php
    /**
     * @inheritdoc
     */
    public function getSluggableFields()
    {
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
use Lch\SeoBundle\Form\SeoType;

/**
 * {@inheritdoc}
 */
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder

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

Then, in the form twigs, add SEO form theme : `{{ form_row(form.seo) }}` to ensure fields rendering and logic.

### Front rendering

Simply add a seo block in your `base.html.twig` in `<head>` section

```twig
    {% block seo %}{% endblock seo %}
```

Then, override this block on each page you want to display SEO information, with a custom Twig function:

- Specific pages :

```twig
    {% block seo %}
        {{ renderSeoTags(app.request) }}
    {% endblock seo %}
```

- Entity pages :
```twig
    {% block seo %}
        {{ renderSeoTags(solution) }}
    {% endblock seo %}
```



### Persistence
So far, add call to `$this->get('lch.seo.tools')->seoFilling()` on controller before persist to ensure data will be set. Will be replaced with doctrine event later
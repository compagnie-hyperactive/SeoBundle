# MediaBundle

## Installation

## Configuration
Add SEO form theme
  form_themes:
  # Order is important here
    - 'LchSeoBundle:form:fields.html.twig'
        
Add Seoable to any entity you want to have SEO settings. This include
 - seoTitle
 - seoDescription
 - slug field 
 
 Implements SeoInterface and fill 2 methods
 1. getSluggableFields for getting fields needed to build slug
 2. getRouteFields to build route
 
We assume that a unique constraint/index is set on slug field, or slug fields collection if more than one.
 
Add SeoType to the Seoable entity type
entity must implement SeoInterface

Add call to $this->get('lch.seo.tools')->seoFilling on controller before persist to ensure data will be set
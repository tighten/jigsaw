![](https://cloud.githubusercontent.com/assets/357312/25055001/5603687e-212e-11e7-8fad-0b33dbf7fb71.png)

Simple static sites with Laravel's [Blade](https://laravel.com/docs/blade).

For documentation, visit http://jigsaw.tighten.co/docs/installation/

---

### Upgrading from an earlier version?

__Version 1.0 includes a change to the way site variables are referenced in your templates.__

Site variables defined in `config.php`, as well as any variables defined in the YAML front matter of a page, are now accessible under the `$page` object, rather than by referencing the variable name itself. Blade templates that include variables will need to be updated so that all variables are prefixed with `$page->`.

Check out http://jigsaw.tighten.co/docs/upgrading/ for an example.


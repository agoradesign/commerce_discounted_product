Commerce Discounted Product
===========================

This module extends Drupal Commerce by storing information about which products
are affected by promotions, in order to be able to query currently discounted
products - and further to be able to build blocks and/or pages based on that
information.

The Commerce Discounted Product module does not try to pre-calculate prices,
neither it saves any pricing data at all. What it does, is to try to solely
determine, which products are affected by the existing promotion entities. It is
designed for rather simple use cases, where you'll define promotion rules for a
number of specific products, or for products of a certain category, tag, etc.
In other words - an usage of the Commerce Promotion module that fit for the
majority of online shops.

Given the fact that both the promotion and pricing system of Commerce is nearly
endlessly extensible and flexible, it's in fact impossible to have this module
cover every use case and working in every setup. There are lots of advanced use
cases that cannot by covered by this module. E.g. if you define conditions based
on customer context (user role, address, etc) - this module won't deliver the
desired result most likely. This module works under the assumption that every
user is treated equally regarding promotions rules. It still could help in these
situations though, as long as you take care of these conditions, when you build
your blocks upon this module's information.

Another limitation is that this module is designed to work against product
entities only, not any purchasable entity. As described above, it's already to
complex to find the right conditions, if you stick to products only. Supporting
any custom purchasable entity would even be harder.

And yes, the description above is correctly speaking of "products" and not
"product variations", although the latter one are the purchasable entities.
Variations do not exist on their own, they are always owned by their parent
product. That's the concept of Commerce Product. So you won't ever render a
block listing product variations, only products. You'll also never define
Promotion conditions based on variation selection, but rather on products. 

The module will by default only evaluate promotions based on conditions that are
shipped with Commerce. But the good news is, that it is made extensible, so that
you can add your own logic for your custom conditions. E.g. if you write a
condition plugin that let you select products based on a referenced Taxonomy
Term, you'll be able to easily add the necessary logic to get these rules
handled by this module.

The module is currently not hosted on drupal.org. It's currently in an
experimental state. We have to find out first, if we can cover at least such a
high amount of use cases, so that it makes any sense at all to create a "real"
Drupal project.
In the meantime, the module will be hosted on Github exclusively:
[Githup repo](https://github.com/agoradesign/commerce_discounted_product)

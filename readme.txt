=== Google Referrer Checker ===
Contributors: codyswann, gunnertech
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=S6HYHZA546HLW&lc=US&item_name=Gunner%20Technology&item_number=google%2dreferrer%2dchecker&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: google, seo
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 0.3.1

A plugin that coaxes google to index sites that link to your site.

== Description ==

Google Referrer Checker is based on an idea and script from Russ Jones (@rjonesx) on [SEOMoz](http://www.seomoz.org/blog/set-it-and-forget-it-seo-chasing-the-elusive-passive-seo-dream).

Here is how he describes it:

We often joke that "Google knows everything." While we can lament the loss of privacy and liberty, there is one thing that I do want Google to know about - my links. I want them to know about as many links pointing to my site as possible. Unfortunately, Google misses out on a good portion of the web. Well, what if you could find links that Google hasn't necessarily found, and then make sure that Google does index them and count them?

If you were go into your Google Analytics right now and export all of the pages that have sent visitors to your site since your website's inception, what percentage of them do you think will have been indexed by Google? 90%, 95%, 99%? Sure, it will probably vary from site to site, especially given how many different sites out there have sent traffic to you, but there are likely to be a handful that Google never got around to crawling. Our goal with this first set-it and forget-it tactic is to find the pages that refer traffic to your site on-the-fly and make sure if they have a link, that Google knows about it.

Ideally, our automated the script would:

* Record every referrer from other sites.
* Spider that site to see if it actually has a real, followed link.
* Check to see if Google had cached that referring page with the followed link.
* Coax Google to reindex that page if it had not yet found the link.
* Continue to check to see if Google had cached the referring page.

This is actually quite easy to accomplish programmatically. The first three steps are done every day by tools regularly used by SEOs. The only difficult part is finding a way to encourage Google to visit the referring pages it has not yet indexed. We can solve this by simply having a widget on the page that displays those referrers, essentially an "As Seen On" bulleted list of pages that had linked to your site, but had not yet been indexed.

This plugin does just that. And adds a simple widget that will display the unindexed links.

You can see it in action on one of our [site](http://gunnertech.com/2012/02/google-referrer-checker-a-wordpress-plugin/).

For any more questions, please go to Gunner Technology's post: [Google Referrer Checker: A WordPress Plugin](http://gunnertech.com/2012/02/google-referrer-checker-a-wordpress-plugin/) and leave a comment.


[youtube http://www.youtube.com/watch?v=9dZJIC_asGE]

Requires WordPress 3.0 and PHP 5. 


== Installation ==

1. Upload the files to the `/wp-content/plugins/google-referrer-checker/` directory
1. Activate the "Google Referrer" plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==



== Screenshots ==

1. Sample widget showing links that haven't been indexed yet.
2. Admin panel showing links that haven't been indexed yet.
3. Admin panel showing hosts that have been banned from being excluded.

== Changelog ==

= 0.1 =
* Initial release

= 0.2 =
* Adds Gunner Technology as a contributor
* Adds short description to readme

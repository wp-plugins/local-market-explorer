=== Local Market Explorer ===
Contributors: amattie, jmabe
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178626
Feedback page link: http://localmarketexplorer.uservoice.com/
Tags: zillow, walk score, schools, education.com, real estate, local, city data, yelp, teachstreet, nile guide
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 3.0

This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single
page in WordPress.

== Description ==

This plugin allows for WordPress to load in data from the following APIs:

* [Zillow](http://www.zillow.com)
* [Education.com](http://www.education.com)
* [Walk Score](http://www.walkscore.com)
* [Yelp](http://www.yelp.com)
* [TeachStreet](http://www.teachstreet.com)
* [Nile Guide](http://www.nileguide.com)

The data from the different APIs is then presented on a single page that is dynamically created on the server depending on the
specially-crafted URL that is being accessed. The format of the URL to load the plugin is as follows:
&lt;http://www.example.com/local/_city_/_state_&gt;.

For example, to load the Local Market Explorer for Seattle, WA, you'd simply need to point your browser to
&lt;http://www.example.com/local/seattle/wa&gt;. If you have spaces in your city name, you can use hyphens for the spaces in the URL,
like so: &lt;http://www.example.com/local/rancho-santa-margarita/ca&gt;.

At any time, you can link to any city in any state that you'd like. While not all of the APIs have data for every single city in the United States, you'll find that most cities are sufficiently covered by nearly all of the APIs.

This plugin is open-source donationware. I'm willing to accept and integrate well-written patches into the code, but the continued development of the module (new features, bug fixes, etc) by the plugin author is funded by donations. If you'd like to donate, please [donate via PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178626).

If you'd like to contribute a feature suggestion or need to document a bug, please use the [User Voice forum](http://localmarketexplorer.uservoice.com/) set up specifically for that purpose. With User Voice, each user gets a fixed number of votes that they can cast for any particular bug or feature. The higher the number of votes for an item, the higher the priority will be for that item as development commences on the plugin itself.

== Installation ==

1. Go to your WordPress admin area, then go to the "Plugins" area, then go to "Add New".
2. Search for "local market explorer" (sans quotes) in the plugin search box.
3. Click "Install" on the right, then click "Install" at the top-right in the window that comes up.
4. Go to the "Settings" -> "Local Market Explorer" area.
5. Visit each of the API key links and get your API keys. After you put in each API key, the data will load for the corresponding modules.

== Changelog ==

= 3.0 =
* Performance has been significantly increased by making all of the external data requests in parallel instead of in series.
* The modules can now be used on individual pages and posts instead of only on the Local Market Explorer virtual pages.
* The HTML markup and CSS styling has been significantly pruned so that skinning is easier and so that the default styles work better with many more themes.
* The admin UI has been significantly enhanced to make it easier to use.
* A neighborhood module has been added on city pages that links to all the neighborhoods within that city.
* Support for canonical link tags has been added so that search engines will better index the true URL and won’t see duplicate content within your domain.
* This version works way better with some of the more obscure WordPress installs as well as with WordPress 3.x.
* Support for XML sitemaps has been added via the Google XML Sitemaps plugin.
* Lots of bugs have been fixed relating to data not displaying when it should and blank data displaying when it shouldn’t.
* More charts and data have been added to a few of the modules.

= 2.1 =
* Added option to link to an IDX page per area
* Fixed issues with template selection so that the plugin will more reliably select the page template (not the post template)
* Fixed bug where empty areas were getting saved

= 2.0.2 =
* Fixed bug where beds / baths were transposed in recent sales activity module

= 2.0.1 =
* Fixed issue with the widget not showing up in admin / not showing areas
* Temporarily removed some of the market stats for zip codes (pending a bug fix by Zillow)

= 2.0 =
* Cached some API requests (where allowed) to make module load faster
* Added support for displaying data for neighborhoods and zips
* Added ability to reorder modules
* Added ability to put HTML (YouTube videos, etc) into the area descriptions
* Usability improvements in the admin
* Added new market data stats (provided by Zillow)
* Added ability to turn off Flickr, Schools, and Market stats panel

= 1.1 =
* Added ability to pull in TeachStreet data

= 1.0.4 =
* Fixed typo since 1.0.2 that caused Thesis theme not to work properly

= 1.0.3 =
* Fixed issue with Zillow API where an undesired city could be loaded if the city name had a space in it
* Changed "What's a Zestimate" to "What's a Zindex" in the disclaimer

= 1.0.2 =
* Fixed another bug with two links in Education.com module (specifically, cities with spaces and anything in California, Colorado, or Arizona)
* Added handling for Thesis theme and any other theme that has a file called "custom_template.php" instead of "page.php" -- new fallback is to "post.php" if neither of those exist
* Added handling for what seems to be a Walk Score tile duplication issue where the tile is getting placed on the page twice (due to their script?)

= 1.0.1 =
* Updated installation instructions
* Fixed bug with links in Education.com module

== Frequently Asked Questions ==

= Can I customize the styling and display format? =

Yes. All of the styles are controlled via an external CSS stylesheet named lme-client.css (located in the 'css' folder). You can
easily override any of the styles in there. Be aware, however, that the default styles were created to be compliant with all of
the branding requirements of the different APIs. It's possible that overriding any of the styles could put you out of compliance
with the API provider(s).

= How do I draw attention to the pages for my target markets? =

There are a number of pre-built images you can use to use as calls to action on your sidebar or anywhere else. The images are
available in the following colors: black, blue, green, orange, and red. The images can be found in the following folder:
http://www.yoursite.com/wp-content/plugins/local-market-explorer/images/badges

= How to alter image colors for the sidebar module to match my site? =

We have already given you a few sample colors to choose from but if you want even more control to integrate these buttons to
match your blog, you can do so with almost all image software. To do so in Adobe Photoshop, just open the image and go to 

Image > Adjustments > Hue/Saturation (Ctrl + U) 

Adjust the sliders to match your site colors. Hue will change the color profile (blue to purple, for example) and the saturation
is how strong or vibrant that color is. 

If you do not have access to Photoshop, this can also be accomplished with GIMP, a free image manipulation software.
(Instructions are here: http://docs.gimp.org/en/gimp-tool-hue-saturation.html)

= How do I add a sidebar module listing my target markets? =

From your wordpress admin interface, simply navigate to Appearance -> Widgets, then you can drag + drop the "LME Widget" from
the "Available Widgets" to a sidebar on the right (such as "Sidebar 1"). Once the widget is placed, you can click the
down-arrow on the newly placed widget to customize the Title and Badge.

= The Market Activity module is not getting populated with recent sales data - why? =

The module is driven by a private API call that needs permissions to be granted to a specific Zillow API key.
To request access to this API, simply fill out the API upgrade form located
[here](http://www.zillow.com/webservice/APIUpgradeRequest.htm) and select "Local Market Explorer Wordpress Plugin" in the API
request type field. Once the request is processed, your market activity module should populate automatically with recent sales
data.

== Screenshots ==

1. A screenshot of the module for Seattle, WA.

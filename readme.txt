=== Local Market Explorer ===
Contributors: amattie, jmabe, zillow
Tags: zillow, flickr, walk score, schools, education.com, real estate, local information, city data, yelp
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 1.0-b19

This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single
page in WordPress.

== Description ==

**REQUIRES PHP 5**

This plugin allows for WordPress to load in data from the following APIs:

* [Zillow](http://www.zillow.com)
* [Education.com](http://www.education.com)
* [Flickr](http://www.flickr.com)
* [Walk Score](http://www.walkscore.com)
* [Yelp](http://www.yelp.com)

The data from the different APIs is then presented on a single page that is dynamically created on the server depending on the
specially-crafted URL that is being accessed. The format of the URL to load the plugin is as follows:
&lt;http://www.example.com/local/_city_/_state_&gt;.

For example, to load the Local Market Explorer for Seattle, WA, you'd simply need to point your browser to
&lt;http://www.example.com/local/seattle,wa&gt;. If you have spaces in your city name, you can use hyphens for the spaces in the URL,
like so: &lt;http://www.example.com/local/rancho-santa-margarita/ca&gt;.

At any time, you can link to any city in any state that you'd like. While not all of the APIs have data for every single city in the United
States, you'll find that most cities are sufficiently covered by nearly all of the APIs.

== Installation ==

1. Extract `local-market-explorer.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress admin
3. Go to Settings => LME Options in the admin area and fill in the necessary API keys

== Frequently Asked Questions ==

= How do I use the module after I install it? =

The module is loaded / activated when the URL in your browser location bar matches the format of
&lt;http://www.yourblog.com/local/_city_/_state_&gt;. In other words, to load the module for Seattle, you'll want to point
your browser to / link to &lt;http://www.yourblog.com/local/seattle/wa&gt;. See the "Description" tab for more info.

= Can I specify a ZIP or a neighborhood instead of a city? =

The Local Market Explorer plugin does not currently let you specify a neighborhood or ZIP, but it's planned for the next iteration of this plugin.

= Can I customize the styling and display format? =

Yes. All of the styles are controlled via an external CSS stylesheet named lme-client.css (located in the 'includes' folder). You can easily override any of the styles in there. Be aware, however, that the default styles were created to be compliant with all of the branding requirements of the different APIs. It's possible that overriding any of the styles could put you out of compliance with the API provider(s).

= Do I have to show all of the panels? =

No. You can turn off the About, Market Activity, Walk Score, and Yelp modules via the Local Market Explorer admin section of your WordPress installation.

= How do I draw attention to the pages for my target markets? =

There are a number of pre-built images you can use to use as calls to action on your sidebar or anywhere else. The images are available in the following colors: black, blue, green, orange, and red. The images can be found in the following folder: http://www.yoursite.com/wp-content/plugins/local-market-explorer/images/badges

= How to alter image colors for the sidebar module to match my site? =

We have already given you a few sample colors to choose from but if you want even more control to integrate these buttons to match your blog, you can do so with almost all image software. To do so in Adobe Photoshop, just open the image and go to 

Image > Adjustments > Hue/Saturation (Ctrl + U) 

Adjust the sliders to match your site colors. Hue will change the color profile (blue to purple, for example) and the saturation is how strong or vibrant that color is. 

If you do not have access to Photoshop, this can also be accomplished with GIMP, a free image manipulation software. (Instructions are here: http://docs.gimp.org/en/gimp-tool-hue-saturation.html)

= How do I add a sidebar module listing my target markets? =

From your wordpress admin interface, simply navigate to Appearance -> Widgets, then you can drag + drop the "LME Widget" from the "Available Widgets" to a sidebar on the right (such as "Sidebar 1"). Once the widget is placed, you can click the down-arrow on the newly placed widget to customize the Title and Badge.

= My sidebar is not widgetized - how do I use this plugin on my blog? =

You can always direct traffic to any city by simply linking to the page from within a blog post. For instance, you could link the word "Seattle" within a blog post to http://www.yoursite.com/local/Seattle/WA/. Alternatively, if you are a real estate agent specializing in Sammamish, Issaquah, and Redmond, here is some sample code to place a module in your sidebar that links to all your target markets:

&lt;p align="center"&gt;&lt;img src="http://www.yoursite.com/wp-content/plugins/local-market-explorer/images/badges/120_lmegraph_orange.gif"&gt;&lt;/p&gt;
&lt;ul&gt;
&lt;li&gt;&lt;a href="http://www.yoursite.com/local/Sammamish/WA/"&gt;Sammamish&lt;/a&gt;&lt;/li&gt;
&lt;li&gt;&lt;a href="http://www.yoursite.com/local/Redmond/WA/"&gt;Redmond&lt;/a&gt;&lt;/li&gt;
&lt;li&gt;&lt;a href="http://www.yoursite.com/local/Issaquah/WA/"&gt;Issaquah&lt;/a&gt;&lt;/li&gt;
&lt;/ul&gt;

= The Market Activity module is not getting populated with recent sales data - why? =

The module is driven by a private API call that needs permissions to be granted to a specific Zillow API key. To request access to this API, simply fill out the API Upgrade form located here and select "Local Market Explorer Wordpress Plugin" in the API Request Type field. Once the request is processed, your market activity module should populate automatically with recent sales data.

== Screenshots ==

1. A screenshot of the module for Seattle, WA.

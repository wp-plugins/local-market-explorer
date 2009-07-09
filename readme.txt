=== Local Market Explorer ===
Contributors: amattie, jmabe, zillow
Tags: zillow, flickr, walk-score
Requires at least: 2.0
Tested up to: 2.8
Stable tag: 1.0-b9

This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single
page in WordPress.

== Description ==

This plugin allows for WordPress to load in data from the following APIs:

* [Zillow](http://www.zillow.com)
* [Education.com](http://www.education.com)
* [Flickr](http://www.flickr.com)
* [Walk Score](http://www.walkscore.com)
* [Yelp](http://www.yelp.com) (coming soon)

The data from the different APIs is then presented on a single page that is dynamically created on the server depending on the
specially-crafted URL that is being accessed. The format of the URL to load the plugin is as follows:
&lt;http://www.example.com/local/_city_,_state_&gt;.

For example, to load the Local Market Explorer for Seattle, WA, you'd simply need to point your browser to
&lt;http://www.example.com/local/seattle,wa&gt;. If you have spaces in your city name, you can use hyphens for the spaces in the URL,
like so: &lt;http://www.example.com/local/rancho-santa-margarita,ca&gt;.

At any time, you can link to any city in any state that you'd like. While not all of the APIs have data for every single city in the United
States, you'll find that most cities are sufficiently covered by nearly all of the APIs.

== Installation ==

1. Extract `local-market-explorer.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress admin
3. Go to Settings => LME Options in the admin area and fill in the necessary API keys

== Frequently Asked Questions ==

= Can I specify a zip or a neighborhood instead of a city? =

This isn't currently possible, but it's something we'd like to do in the future once the necessary data is available in the
APIs.

= Can I customize the styling and display format? =

Yep. All of the styles are controlled via an external CSS stylesheet named lme-client.css (located in the 'includes' folder).
You can easily override any of the styles in there. Be aware, however, that the default styles were created to be compliant with
all of the branding requirements of the different APIs. It's possible that overriding any of the styles could put you out of
compliance with the API provider(s).

= Do I have to show all of the panels? =

Nope. You can turn off the "About" panel, the "Market Activity" panel, and / or the "Walk Score" panel in the Local Market
Explorer admin section of WordPress.

== Screenshots ==

1. A screenshot of the module for Seattle, WA.

# LittleBot Netlify

Connect your WordPress website to [Netlify](https://www.netlify.com/) by triggering stage and or production build hooks on post save and or update.

## Installation

* Download or clone repository
* Move `littlebot-netlify` to your plugins directory or zip and upload
* Activate plugin
* Create at least one site at Netlify
* [Create a build hook](https://www.netlify.com/docs/webhooks/) for each site (or just one if you're just using one site)
* Add build hook to the Settings > LittleBot Netlify
* Your WordPress site will call your build hook(s) when publishing, updating or deleting a post

## Gatsby + WordPress + Netlify Starter

[Gatsby + WordPress + Netlify Starter](https://github.com/justinwhall/gatsby-wordpress-netlify-starter) is a plug and play starter to get up and running with continuous deployment from your WordPress site to Netlify with Gatsby.

## Q & A

`Q` **Do you need two sites at Netlify?**

`A` No. This plugin will call your build hook and build your Gatsby (or whatever) site no matter what. The starter mentioned above facilitates a _two_ environment Gatsby set up but other than that, this plugin is totally _front end agnostic_ and you could just as easy trigger one build hook by only adding one build hook URL.

`Q` **Does this plugin support Gutenberg?**

`A` This plugin supports both GutenLOVERS and GutenHATERS. How? It supports Gutenberg as that is what the WordPress editing experience is now. Don't like Gutenberg? This plugin also supports the Classic Editor.

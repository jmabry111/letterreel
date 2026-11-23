=== LetterReel ===
Contributors: jmabry111
Tags: block, typography, image, hero, animation
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A block that shows a different photo through each letter of a big word, with a gentle entrance animation. It always fits on one line.

== Description ==

LetterReel adds one block, **LetterReel Letters**: a big word or short title where each letter is filled with a photo from your Media Library. It is made for home-page heroes, event titles and section openers.

* **Photos in the letters:** pick any number of photos. The letters take turns using them, and a photo that comes round again shows a different part of the picture.
* **Focal points:** drag a dot on each photo to choose what shows inside the letters.
* **Always fits:** the word is sized from the width of its container, so it stays on one line from phones to wide screens. You set the largest size.
* **Motion that respects visitors:** the letters rise in one after another, then the photos drift slowly. Animation can be switched off, and it is always off for visitors whose device asks for reduced motion.
* **Accessible:** screen readers read the word itself, not one letter at a time. You choose the heading level (or a plain paragraph). Text colour, outline and the colour behind the photos are all yours to set, so you can keep contrast checkers happy.
* **Font:** uses the bundled Alfa Slab One, a heavy slab serif that shows a lot of each photo, or your theme's heading font.
* **No settings page, no tracking, no external requests.** Everything is served from your own site.

== Installation ==

1. Install LetterReel from **Plugins > Add New**, or upload the `jmabry111-letterreel` folder to `/wp-content/plugins/`.
2. Activate it.
3. In the block editor, add the **LetterReel Letters** block (in the Design category, or search for "LetterReel").
4. In the block settings: type your word, click **Choose photos**, and drag each photo's focal point onto the subject.

== Frequently Asked Questions ==

= How long can the text be? =

Any length fits on one line, but the text gets smaller as it gets longer. One word or a title of about 10 letters looks best, especially on phones.

= Which photos work best? =

Bright, colourful photos with the subject near the middle. Each letter only shows a narrow slice, so busy or dark photos can be hard to read.

= Why does an accessibility checker complain about the letters? =

Checkers cannot see the photos. They compare the text colour (Styles tab) with **Colour behind the photos** (Appearance panel). Choose two colours that contrast, for example yellow text with black behind the photos.

= Can I turn off the animation? =

Yes: switch off **Animate** in the Appearance panel. It is also always off for visitors who have turned on "reduce motion" on their device.

= Does it work with my theme's font? =

Yes. Choose **Theme's heading font** in the Appearance panel. The block measures the word after the font loads and fits it exactly. Heavy fonts show more of each photo.

= Do the photos need alt text? =

Not for this block: screen readers read the word, and the photos are decoration inside it.

== Screenshots ==

1. A photo-filled word on a dark hero: the letters rise in one after another, then the line underneath fades in.
2. Editing: the word, the line underneath, the heading level and the photos, each with its own focal point.
3. On a phone every word still fits on one line: the bundled font, the theme's font without an outline, and no photos.

== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
First release.

== Credits ==

Alfa Slab One by JM Solé (jmsole.cl), licensed under the SIL Open Font License 1.1. The licence is included in `fonts/OFL.txt`.

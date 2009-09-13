<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007-2008 Harvey Kane <code@ragepank.com>
 * Copyright 2007-2008 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

/* Sitemap filter */
Jojo::addFilter('jojo_sitemap', 'sitemap', 'jojo_forum');

/* XML filter */
Jojo::addFilter('jojo_xml_sitemap', 'xmlsitemap', 'jojo_forum');

/* Search Filter */
Jojo::addFilter('jojo_search', 'search', 'jojo_forum');

/* Hook for 'recent posts' on user profile */
Jojo::addHook('profile_bottom', 'profile_bottom', 'jojo_forum');

$_provides['pluginClasses'] = array(
        'JOJO_Plugin_Jojo_forum'     => 'Forum - Forum Listing and View',
        'jojo_plugin_jojo_forum_rss' => 'Forum - RSS Feed'
        );

/* Register URI patterns */
Jojo::registerURI("forums/[action:reply]/[id:integer]",                          'Jojo_Plugin_Jojo_forum');     // "forums/reply/1234/"
Jojo::registerURI("forums/[action:new]/[id:integer]",                            'Jojo_Plugin_Jojo_forum');     // "forums/new/"

Jojo::registerURI("[action:forums]/[forumurl:string]/p[pagenumber:[0-9]+]",      'Jojo_Plugin_Jojo_forum');     // "forums/url-of-forum/p2/"
Jojo::registerURI("[action:forums]/[id:integer]/[string]",                       'Jojo_Plugin_Jojo_forum');     // "forums/563/name-of-forum/"
Jojo::registerURI("[action:forums]/[forumurl:((?!rss)string)]",                  'Jojo_Plugin_Jojo_forum');     // "forums/url-of-forum/"


Jojo::registerURI("[action:topics]/[id:integer]/[string]",                       'Jojo_Plugin_Jojo_forum');     // "topics/3532/name-of-topic/"
Jojo::registerURI("[action:topics]/[id:integer]p[pagenumber:integer]/[string]",  'Jojo_Plugin_Jojo_forum');     // "topics/3532p2/name-of-topic/"
Jojo::registerURI("topics/[id:integer]/[string]/[action:subscribe|unsubscribe]", 'Jojo_Plugin_Jojo_forum');     // "/topics/3532/name-of-topic/subscribe/ AND /topics/3532/name-of-topic/unsubscribe/"
Jojo::registerURI("[action:topics]/[id:integer]/[string]/rss",                   'Jojo_Plugin_Jojo_forum');     // "topics/3532/name-of-topic/rss/"
Jojo::registerURI("[action:edit-topic]/[id:integer]/[string]",                   'Jojo_Plugin_Jojo_forum');     // "edit-topic/563/"
Jojo::registerURI("[action:topics]/[id:integer]",                                'Jojo_Plugin_Jojo_forum');     // "topics/3532"

Jojo::registerURI("[action:profiles]/[id:integer]/[string]",                     'Jojo_Plugin_Jojo_forum');     // here for backwards compatibility only
Jojo::registerURI("[action:profiles]/[profilename:string]",                      'Jojo_Plugin_Jojo_forum');     // here for backwards compatibility only

Jojo::registerURI("[action:posts]/[id:integer]/[string]",                        'Jojo_Plugin_Jojo_forum');     // "posts/563/name-of-topic/"
Jojo::registerURI("[action:delete-post]/[id:integer]/[string]",                  'Jojo_Plugin_Jojo_forum');     // "delete-post/563/"
Jojo::registerURI("[action:edit-post]/[id:integer]/[string]",                    'Jojo_Plugin_Jojo_forum');     // "edit-post/563/"

$_options[] = array(
    'id'          => 'forum_posts_per_page',
    'category'    => 'Forums',
    'label'       => 'Forum posts per page',
    'description' => 'Number of posts to show on a forum topic page',
    'type'        => 'integer',
    'default'     => '20',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'forum_topics_per_page',
    'category'    => 'Forums',
    'label'       => 'Forum topics per page',
    'description' => 'Number of topics to show on a forum page',
    'type'        => 'integer',
    'default'     => '25',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'form_num_recentposts',
    'category'    => 'Forums',
    'label'       => 'Number of recent posts teasers to show in the sidebar',
    'description' => 'The number of recent posts to be displayed as snippets in a teaser box on other pages - set to 0 to turn off',
    'type'        => 'integer',
    'default'     => '0',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'form_group_posts',
    'category'    => 'Forums',
    'label'       => 'Show only one recent post teaser per topic',
    'description' => 'Rather than displaying all recent posts from any topic, limit to the first post in most recently active topics',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'forum_intro_text',
    'category'    => 'Forums',
    'label'       => 'Dynamic forum intro text',
    'description' => 'A dynamically built HTML intro paragraph for each forum, which will assist with SEO. Variables to use are [forum], [site].',
    'type'        => 'textarea',
    'default'     => '<strong>[forum]</strong>, a forum topic on [site]. Join in the <em>[forum]</em> discussions on our community forum.',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'forum_topic_intro_text',
    'category'    => 'Forums',
    'label'       => 'Dynamic forum topic intro text',
    'description' => 'A dynamically built HTML intro paragraph for each forum topic, which will assist with SEO. Variables to use are [topic], [forum], [site].',
    'type'        => 'textarea',
    'default'     => '<strong>[topic]</strong>, a forum discussion on [site]. Join us for more discussions on <em>[topic]</em> on our [forum] forum.',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'forum_allow_guest_posts',
    'category'    => 'Forums',
    'label'       => 'Allow guest posts',
    'description' => 'Allows users to post without logging in. this only applies to public forums.',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_forum'
);

$_options[] = array(
    'id'          => 'forum_moderator_groups',
    'category'    => 'Forums',
    'label'       => 'Forum moderator groups',
    'description' => 'A comma separated list of user groups that act as moderators on the forums. Moderators can delete / move / sticky posts.',
    'type'        => 'text',
    'default'     => 'admin',
    'options'     => '',
    'plugin'      => 'jojo_forum'
);
<h1 class="title"><a id='magicparlabel-3'></a>
Saint Developer Guide</h1>
<div class='toc'><div class='tochead part'>Table of Contents</div>

<div class='lyxtoc-1'><a href='#magicparlabel-6' class='tocentry'>1 Introduction</a> <a href='#magicparlabel-6' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-10' class='tocentry'>1.1 Installation</a> <a href='#magicparlabel-10' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-13' class='tocentry'>1.2 Your First Website</a> <a href='#magicparlabel-13' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-15' class='tocentry'>2 Themes</a> <a href='#magicparlabel-15' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-17' class='tocentry'>2.1 Creating a Theme</a> <a href='#magicparlabel-17' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-19' class='tocentry'>2.2 Editing Your Theme</a> <a href='#magicparlabel-19' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-22' class='tocentry'>3 Labels</a> <a href='#magicparlabel-22' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-30' class='tocentry'>3.1 Page Labels</a> <a href='#magicparlabel-30' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-39' class='tocentry'>3.2 Block Labels</a> <a href='#magicparlabel-39' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-46' class='tocentry'>3.3 WYSIWYG Labels</a> <a href='#magicparlabel-46' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-54' class='tocentry'>4 Blocks</a> <a href='#magicparlabel-54' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-65' class='tocentry'>4.1 Scripts and Styles</a> <a href='#magicparlabel-65' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-74' class='tocentry'>4.2 Repeating Blocks</a> <a href='#magicparlabel-74' class='tocarrow'>&gt;</a>

<div class='lyxtoc-3'><a href='#magicparlabel-92' class='tocentry'>4.2.1 Block Settings</a> <a href='#magicparlabel-92' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-3'><a href='#magicparlabel-111' class='tocentry'>4.2.2 Block Arguments</a> <a href='#magicparlabel-111' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-3'><a href='#magicparlabel-140' class='tocentry'>4.2.3 Multiple Views</a> <a href='#magicparlabel-140' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-2'><a href='#magicparlabel-149' class='tocentry'>4.3 Saint Blog</a> <a href='#magicparlabel-149' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-151' class='tocentry'>5 Permissions</a> <a href='#magicparlabel-151' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-1'><a href='#magicparlabel-184' class='tocentry'>6 Controllers</a> <a href='#magicparlabel-184' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-188' class='tocentry'>6.1 Controller Security</a> <a href='#magicparlabel-188' class='tocarrow'>&gt;</a></div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-191' class='tocentry'>7 Models</a> <a href='#magicparlabel-191' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-193' class='tocentry'>7.1 Model Security</a> <a href='#magicparlabel-193' class='tocarrow'>&gt;</a>

<div class='lyxtoc-3'><a href='#magicparlabel-204' class='tocentry'>7.1.1 Patterns</a> <a href='#magicparlabel-204' class='tocarrow'>&gt;</a></div>
</div>
</div>

<div class='lyxtoc-1'><a href='#magicparlabel-207' class='tocentry'>8 Logging</a> <a href='#magicparlabel-207' class='tocarrow'>&gt;</a>

<div class='lyxtoc-2'><a href='#magicparlabel-209' class='tocentry'>8.1 Events</a> <a href='#magicparlabel-209' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-216' class='tocentry'>8.2 Warnings</a> <a href='#magicparlabel-216' class='tocarrow'>&gt;</a></div>

<div class='lyxtoc-2'><a href='#magicparlabel-223' class='tocentry'>8.3 Errors</a> <a href='#magicparlabel-223' class='tocarrow'>&gt;</a></div>
</div>
</div>

<h2 class="section"><span class="section_label">1</span> <a id='magicparlabel-6'></a>
Introduction</h2>
<div class="standard"><a id='magicparlabel-7'></a>
Saint was designed from the ground up to make life easier for developers. To the end user it is merely a fully functional content management system. To the developer it is also a framework of tools that allow easy creation of custom dynamic-content websites. The second premise of Saint's design is that the person trying to edit content is not always a technical person. Thus the programmer may set restrictions on data in ways not possible in other content management systems, preventing an unintended layout modification.</div>

<div class="standard"><a id='magicparlabel-8'></a>
Saint sites are thus meant to be created by a programmer, deployed and then maintained by a non-technical user. The programmer is given tools to speed development and the end user is given a simple but modern AJAX powered interface for editing content.</div>

<div class="standard"><a id='magicparlabel-9'></a>
This guide is meant to act as a basic tutorial. For detailed information about functions, see the API documentation.</div>
<h3 class="subsection"><span class="subsection_label">1.1</span> <a id='magicparlabel-10'></a>
Installation</h3>
<div class="standard"><a id='magicparlabel-11'></a>
Saint is distributed in the form of a compressed archive. Acquire your copy from the Saint website (http://www.saintcms.com/) and extract it to your web root to start. Edit the file &ldquo;config.php&rdquo; and enter the database details you wish to use. If you don't have a database already you'll have to create one; check with your web host if you are unsure of how to proceed. Once the config file is saved you will need to set proper permissions on your logs, media and uploads directories; the web server needs to be able to write to them. This can generally be done via FTP, but you may wish to check with your hosting provider if you are not sure of the proper way to set permissions on your server.</div>

<div class="standard"><a id='magicparlabel-12'></a>
Now point your browser to your web location. If you are developing locally, this is probably 127.0.0.1 or &ldquo;localhost&rdquo;. If you are developing on a live server you will have been given a URL to use to access your website. After accepting the license agreement you should be presented with a form requesting a username, password and e-mail address for the site administrative account. Enter this information and submit the form to finish the install.</div>
<h3 class="subsection"><span class="subsection_label">1.2</span> <a id='magicparlabel-13'></a>
Your First Website</h3>
<div class="standard"><a id='magicparlabel-14'></a>
The Saint install enables a few basic pages linked by menu. These are not the actual Saint interface. They are instead just examples distributed with the system to make development more straightforward. The Saint interface is an overlay which appears in all pages of your site when you are authorized to see it. To access it, navigate to the path &ldquo;/login&rdquo;. Enter your administrative username/password and submit the form to continue. Now you will see the Saint menu in the upper right hand corner. If you haven't already read the Saint user guide, now is the time to do so. It will teach you how to use the Saint interface. When you have completed that, return here and move on to the next section.</div>
<h2 class="section"><span class="section_label">2</span> <a id='magicparlabel-15'></a>
Themes</h2>
<div class="standard"><a id='magicparlabel-16'></a>
As you might expect, editing core files is not necessary (nor advised) to create a custom Saint theme. By default Saint uses files found in the &ldquo;core&rdquo; directory. However, when a theme is selected Saint instead relies on a fallback system: First the theme directory is checked, then the core directory.</div>
<h3 class="subsection"><span class="subsection_label">2.1</span> <a id='magicparlabel-17'></a>
Creating a Theme</h3>
<div class="standard"><a id='magicparlabel-18'></a>
To create a theme, make a new folder in the &ldquo;themes&rdquo; directory. Congratulations! You have made your first Saint theme. To activate it, edit the base config file and change the theme name to match your folder name.</div>
<h3 class="subsection"><span class="subsection_label">2.2</span> <a id='magicparlabel-19'></a>
Editing Your Theme</h3>
<div class="standard"><a id='magicparlabel-20'></a>
You may notice that your theme looks exactly like the vanilla theme. This is because Saint checked your theme directory for files, found none, and fell back to the default interface. Let's say you wish to change the navigation bar. In your themes folder, create a new folder called &ldquo;blocks&rdquo;. Inside that folder, create another called &ldquo;navigation&rdquo;. Copy the file &ldquo;menu.php&rdquo; from &ldquo;core/blocks/navigation&rdquo; to &ldquo;themes/yourtheme/blocks/navigation&rdquo;. Now you can edit the file in your theme directory and the changes will appear when your theme is active.</div>

<div class="standard"><a id='magicparlabel-21'></a>
This idea holds true for any of the blocks, scripts or styles used in Saint. Any of these files found in the active theme directory will override their respective files in the core folder. This allows you to create &ldquo;minimal&rdquo; themes which only alter necessary files. The theme directory is where you will store all your site's files.</div>
<h2 class="section"><span class="section_label">3</span> <a id='magicparlabel-22'></a>
Labels</h2>
<div class="standard"><a id='magicparlabel-23'></a>
The first step to converting your static template to a Saint powered website is to replace all text with labels. Labels are simply areas of text which the system makes editable. They are easy to use. Any place you would normally write plain text, substitute with the following Saint function:</div>

<div class="standard"><a id='magicparlabel-24'></a>
</div>
<div class='float float-listings'><pre>&lt;?php echo Saint::getLabel("labelname","Default text"); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-29'></a>
The output will start as your &ldquo;Default text&rdquo; argument. This text is stored in the database and editable via the Saint web interface. Content output by this function will be the same on every page. For an example of this, see the text in the menu items. These use site-wide labels.</div>
<h3 class="subsection"><span class="subsection_label">3.1</span> <a id='magicparlabel-30'></a>
Page Labels</h3>
<div class="standard"><a id='magicparlabel-31'></a>
At times the need arises to use text that is specific to each page. This can also be accomplished by using the following function instead:</div>

<div class="standard"><a id='magicparlabel-32'></a>
</div>
<div class='float float-listings'><pre>&lt;?php echo $page-&gt;getLabel("labelname","Default text"); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-37'></a>
By using the running page's getLabel function any changes made to one page will not affect other pages. As such you may design a template for use in multiple pages, each with its own data.</div>

<div class="standard"><a id='magicparlabel-38'></a>
As you may have guessed, you can put the same label in multiple locations by referencing the same name. As outlined above the names would not create a conflict, since the way the &ldquo;Page&rdquo; label function works internally is by adding the name of the page to the start of the label. Thus, on a page called &ldquo;blog&rdquo;, a page label called &ldquo;title&rdquo; becomes &ldquo;blog/title&rdquo;. You can use this knowledge to reference page blocks from external locations, simply by requesting their name directly.</div>
<h3 class="subsection"><span class="subsection_label">3.2</span> <a id='magicparlabel-39'></a>
Block Labels</h3>
<div class="standard"><a id='magicparlabel-40'></a>
Block labels are just like page labels except they are specific to individual blocks. Here is an example of usage within a repeating block:</div>

<div class="standard"><a id='magicparlabel-41'></a>
</div>
<div class='float float-listings'><pre>&lt;?php echo $block-&gt;getLabel("labelname","Default text"); ?&gt;</pre></div>

<h3 class="subsection"><span class="subsection_label">3.3</span> <a id='magicparlabel-46'></a>
WYSIWYG Labels</h3>
<div class="standard"><a id='magicparlabel-47'></a>
Normally blocks in Saint are meant to have rigid structure matching each data type. Using them as outlined above the developer can create custom editable content areas which match specific layouts. However, Saint also comes with a popular &ldquo;What You See Is What You Get&rdquo; editor for use in situations where that functionality is wanted. The following code will insert a WYSIWYG editable area:</div>

<div class="standard"><a id='magicparlabel-48'></a>
</div>
<div class='float float-listings'><pre>&lt;?php echo Saint::getLabel("content",array('wysiwyg'=&gt;true); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-53'></a>
The one argument given is the name of the WYSIWYG block. This attribute can be applied to page and block content labels too. You can see the WYSIWYG block at work in the sample home layout.</div>
<h2 class="section"><span class="section_label">4</span> <a id='magicparlabel-54'></a>
Blocks</h2>
<div class="standard"><a id='magicparlabel-55'></a>
Blocks are chunks of code which may be included within the site. These are the place to find any HTML templates you wish to edit. They can, and often do, include other blocks within themselves. In the folder named "Layouts" you will find the root blocks called, as you may have guessed, layouts. They are just like any other block with the exception that when creating a page it is only possible to use one of these blocks as the base. Starting from these root blocks an entire page is generated.</div>

<div class="standard"><a id='magicparlabel-56'></a>
In the "Content" directory you will find blocks meant to go in the main content section of your website. This is not enforced by site code but is merely a convention for where to store your templates.</div>

<div class="standard"><a id='magicparlabel-57'></a>
It is possible to create an entire website in Saint by having simple HTML code in the layout files and using them as basic pages. This, as you might imagine, is not an effective use of Saint; but it demonstrates the simplest possible site. Instead, these blocks are meant to contain layout HTML along with the dynamic Saint data models such as labels, images, galleries, slideshows, or repeating blocks.</div>

<div class="standard"><a id='magicparlabel-58'></a>
To include a block within one of your template files, use the following function:</div>

<div class="standard"><a id='magicparlabel-59'></a>
</div>
<div class='float float-listings'><pre>&lt;?php Saint::includeBlock("name"); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-64'></a>
Where &ldquo;name&rdquo; represents the name of the block you wish to include. This name is relative to the &ldquo;blocks&rdquo; directory and does not include the extension of the file. Thus, a file in the /blocks/content directory called &ldquo;test.php&rdquo; would have a name of &ldquo;content/test&rdquo;. Saint first looks in this base directory for files. If they are not found, it then looks in the same respective directory in the &ldquo;core&rdquo; folder. In this fashion, any core file can be overridden by dropping a file of the same name into the root blocks folder. This is referred to as the &ldquo;user&rdquo; folder. All your site files will be in the user directories, while all the Saint files will be in the core directory. This keeps your site separate from the Saint code.</div>
<h3 class="subsection"><span class="subsection_label">4.1</span> <a id='magicparlabel-65'></a>
Scripts and Styles</h3>
<div class="standard"><a id='magicparlabel-66'></a>
Scripts and styles are included in a similar fashion to blocks. The same rules apply to both the naming convention and the directory precedence. The functions for including these are listed here:</div>

<div class="standard"><a id='magicparlabel-67'></a>
</div>
<div class='float float-listings'><pre>&lt;?php Saint::includeStyle("saint"); ?&gt;
&lt;?php Saint::includeScript("saint"); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-73'></a>
They are located in the &ldquo;styles&rdquo; and &ldquo;scripts&rdquo; directories, respectively. In order to use </div>
<h3 class="subsection"><span class="subsection_label">4.2</span> <a id='magicparlabel-74'></a>
Repeating Blocks</h3>
<div class="standard"><a id='magicparlabel-75'></a>
Repeating blocks are the most powerful construct in a dynamic Saint website. Do you have any content that repeats? Virtually all websites do. Blog posts, news items, calendar events, interviews, links... these are all items which should be inserted into repeating blocks. What is the advantage in doing so? Repeating blocks can be added and removed through the site user interface. Let's work with a more concrete example by creating our own test blog.</div>

<div class="standard"><a id='magicparlabel-76'></a>
To include a repeating block we pass an optional array parameter containing arguments. If the parameter &ldquo;repeat&rdquo; is set to more than one then the block will automatically repeat. Create a file called &ldquo;test.php&rdquo; in the blocks/layouts directory containing the following code:</div>

<div class="standard"><a id='magicparlabel-77'></a>
</div>
<div class='float float-listings'><pre>&lt;?php Saint::includeBlock("content/test",array("repeat"=&gt;4)); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-82'></a>
Next, create a file called &ldquo;test.php&rdquo; in the blocks/content directory containing the following code:</div>

<div class="standard"><a id='magicparlabel-83'></a>
</div>
<div class='float float-listings'><pre>&lt;h3&gt;&lt;?php echo $block-&gt;getLabel("title","test title"); ?&gt;&lt;/h3&gt;
&lt;p&gt;&lt;?php echo $block-&gt;getLabel("content","test content"); ?&gt;&lt;/p&gt;</pre></div>


<div class="standard"><a id='magicparlabel-89'></a>
Finally, assign a page to your test layout using the Saint web interface.</div>

<div class="standard"><a id='magicparlabel-90'></a>
At first you should see a note that there are no blocks found matching your criteria. This is because we haven't added any. Start editing the current page and click the &ldquo;Add New Test&rdquo; button. You will see editable blocks of text named &ldquo;title&rdquo; and &ldquo;content&rdquo; with your default values as entered above. You can create any number of fields in this fashion. Labels, WYSIWYG areas, and other blocks can all be placed within repeating blocks.</div>

<div class="standard"><a id='magicparlabel-91'></a>
In this scenario we used the Saint content function getBlockLabel for our text. This means that all the labels will be unique within their blocks. As with page labels, the way this works is by adding the name of the block, along with the block ID, to the front of the label name. The name of the block represents the template used and the number represents the automatically generated ID from the database for this specific instance of the block.</div>
<h4 class="subsubsection"><span class="subsubsection_label">4.2.1</span> <a id='magicparlabel-92'></a>
Block Settings</h4>
<div class="standard"><a id='magicparlabel-93'></a>
By default repeating blocks will be sorted by their automatically generated database ID. This will work for many types of data, but at times you'll need to change it. In our blog post example above we may wish to add a date setting and seo tags to each post. &ldquo;Ok,&rdquo; you think, &ldquo;It's time to write some SQL.&rdquo; Not quite. All the block settings in Saint are handled via XML config files. By convention, each repeating block will have its settings in a file of the same name using the xml extension. This is just convention; the entire blocks directory is scanned for all xml files, and all the configuration can be done from a single file if you wish. Following convention, we would create a file called &ldquo;post.xml&rdquo; in the blocks/content directory containing the following code:</div>

<div class="standard"><a id='magicparlabel-94'></a>
</div>
<div class='float float-listings'><pre>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;block&gt;
	&lt;name&gt;content/blog-post&lt;/name&gt;
	&lt;setting datatype="datetime"&gt;postdate&lt;/setting&gt;
	&lt;setting&gt;tags&lt;/setting&gt;
&lt;/block&gt;</pre></div>


<div class="standard"><a id='magicparlabel-104'></a>
Now after refreshing the page you will be able to edit block settings in the right hand column of the &ldquo;edit block&rdquo; interface. You can display these settings within a block template by using the following function:</div>

<div class="standard"><a id='magicparlabel-105'></a>
</div>
<div class='float float-listings'><pre>&lt;?php echo $block-&gt;get($setting); ?&gt;</pre></div>


<div class="standard"><a id='magicparlabel-110'></a>
Where $setting represents the name of the setting, such as &ldquo;postdate&rdquo;. The most useful feature of settings is that they can be used to filter and sort the repeating blocks.</div>
<h4 class="subsubsection"><span class="subsubsection_label">4.2.2</span> <a id='magicparlabel-111'></a>
Block Arguments</h4>
<div class="standard"><a id='magicparlabel-112'></a>
Blocks can be passed a number of arguments to configure their inclusion. Let's look at an example based on the Saint blog:</div>

<div class="standard"><a id='magicparlabel-113'></a>
</div>
<div class='float float-listings'><pre>$arguments = array(
	"paging" =&gt; true,
	"repeat" =&gt; 2,
	"order" =&gt; "DESC",
	"orderby" =&gt; "postdate",
	"category" =&gt; "News",
	"matches" =&gt; array("enabled","1"),
);
Saint::includeBlock("blog/post",$arguments);</pre></div>


<div class="standard"><a id='magicparlabel-126'></a>
Let's start from the top. By setting &ldquo;paging&rdquo; to true the Saint automatic paging system is enabled. Users will now see a &ldquo;next&rdquo; and &ldquo;previous&rdquo; button when applicable as well as a listing of pages. </div>

<div class="standard"><a id='magicparlabel-127'></a>
The &ldquo;repeat&rdquo; argument sets how many blocks are included. In this example, we have two blog posts per page. </div>

<div class="standard"><a id='magicparlabel-128'></a>
The &ldquo;order&rdquo; argument will be either &ldquo;ASC&rdquo; or &ldquo;DESC&rdquo; and is passed to the database for sorting. </div>

<div class="standard"><a id='magicparlabel-129'></a>
The &ldquo;orderby&rdquo; argument must match one of the columns of the block table. There are two built in fields, the auto incremented 'id' and a boolean 'enabled'. Any other field must be added as a block setting. In this case, we are using the block setting &ldquo;postdate&rdquo; which is a datetime field representing our posted date.</div>

<div class="standard"><a id='magicparlabel-130'></a>
The &ldquo;category&rdquo; argument accepts either a scalar category name or an array of category names to match (Note: This is actually not true at the moment, I've just noticed. I must fix this before release. At the moment it just accepts a single scalar category name).</div>

<div class="standard"><a id='magicparlabel-131'></a>
The &ldquo;matches&rdquo; argument accepts either a single array of parameters to match, as shown in the example, or a multidimensional array of parameters as shown below. In addition, each set of parameters accepts an optional third argument defining the type of matching to use.</div>
<div class='float float-listings'><pre>"matches" =&gt; array(
	array("enabled","1"),
	array("postdate","2012-03-13 18:23:45","&gt;"),
),</pre></div>


<div class="standard"><a id='magicparlabel-139'></a>
This would result in the system only displaying posts from a date later than the one given. This field accepts any comparison operator which works with their database. As with the &ldquo;orderby&rdquo; argument, all names given must be block setting names.</div>
<h4 class="subsubsection"><span class="subsubsection_label">4.2.3</span> <a id='magicparlabel-140'></a>
Multiple Views</h4>
<div class="standard"><a id='magicparlabel-141'></a>
The repeating block system is extremely useful for rapid development, but in many cases we don't just want to display the data in one location or in one format. You've created a blog, but now you want a &ldquo;Latest News&rdquo; preview box on your front page. You want an RSS feed of your posts. So, open up phpMyAdmin, examine the table generated by Saint for your block, and write up an SQL query to extract the data you need. No, sorry, I got caught up in the old way for a minute. All you have to do is pass Saint an additional argument defining a view template. Have a look at this example:</div>

<div class="standard"><a id='magicparlabel-142'></a>
</div>
<div class='float float-listings'><pre>$arguments['view'] = "blog/rss-item";
Saint::includeBlock("blog/post",$arguments);</pre></div>


<div class="standard"><a id='magicparlabel-148'></a>
It's very similar to the above example, with the block name and $arguments array being the same as before with the addition of the view shown. This new field is the block to use as a template for our data. Put simply, all the &ldquo;blog/post&rdquo; blocks matching $arguments will be passed to the &ldquo;blog/rss-item&rdquo; template for display. In this way, we can add multiple views of any given block data.</div>
<h3 class="subsection"><span class="subsection_label">4.3</span> <a id='magicparlabel-149'></a>
Saint Blog</h3>
<div class="standard"><a id='magicparlabel-150'></a>
As you've likely guessed, the blog system included with Saint is a block with multiple views as described above. If you have any doubts about how fast it is to develop with Saint, have a look at the code for the included blog. All the tedious parts of coding are handled by the Saint framework; the blog code itself merely defines ways in which blog posts differ from standard blocks instead of recreating the functionality from the ground up This is just a small example of the power of Saint. Save yourself time and frustration by utilizing this power in all your new web applications.</div>
<h2 class="section"><span class="section_label">5</span> <a id='magicparlabel-151'></a>
Permissions</h2>
<div class="standard"><a id='magicparlabel-152'></a>
Permissions in Saint are handled through groups. By default new groups do not have any rights, so you must add them in the core configuration file. This file is &ldquo;core/config.php&rdquo; in keeping with the usual Saint convention. In this case both the core and the user versions are loaded but any flags set in the user file will override ones of the same name in the core config file. There are four default groups: administrator, moderator, user, and guest. Say you wanted to add a custom group to the list called &ldquo;friends&rdquo; who are able to view the website while you are still building it. You might use the following code:</div>

<div class="standard"><a id='magicparlabel-153'></a>
</div>
<div class='float float-listings'><pre>$saint_group_access = array(
	'friends' =&gt; array('maintenance-mode'),
	'administrator' =&gt; array([...]),
	[...]
);</pre></div>


<div class="standard"><a id='magicparlabel-162'></a>
After this change, any users you put into the &ldquo;Friends&rdquo; category will be able to view the website even when it is flagged for maintenance.</div>

<div class="standard"><a id='magicparlabel-163'></a>
Let's take it a step further. Now you want to give friends the ability to post on your blog. Modify the code like so:</div>

<div class="standard"><a id='magicparlabel-164'></a>
</div>
<div class='float float-listings'><pre>$saint_group_access = array(
	'friends' =&gt; array('maintenance-mode','add-blog-post'),
	'administrator' =&gt; array([...]),
	[...]
);</pre></div>


<div class="standard"><a id='magicparlabel-173'></a>
We're not quite done yet. Since a blog post is just a standard block, the default permission checked is actually 'add-block'. You'll need to add your own security check in the controller using the following code:</div>

<div class="standard"><a id='magicparlabel-174'></a>
</div>
<div class='float float-listings'><pre>if (Saint::getCurrentUser()-&gt;hasPermissionTo('add-blog-post')) {
	// Save post here
} else {
	Saint::logError("User ".Saint::getCurrentUsername() . " attempted to add blog post but was denied access.",__FILE__,__LINE__);
}</pre></div>


<div class="standard"><a id='magicparlabel-183'></a>
Saint::getCurrentUser() returns a model of our logged in user. The Saint_Model_User::hasPermissionTo(&ldquo;string&rdquo;) function returns true/false based on whether or not a user of that group is allowed to perform the given task. But wait, you ask, where do I put that code? Let's continue on to the next section to find out.</div>
<h2 class="section"><span class="section_label">6</span> <a id='magicparlabel-184'></a>
Controllers</h2>
<div class="standard"><a id='magicparlabel-185'></a>
By now you should know enough about Saint for most situations. One thing we haven't covered yet is handling user input in your custom applications. There's no reason that you can't handle it in the traditional way, manipulating data inside of your block templates. If you are building a large application you may wish to improve organization by using the same Model View Controller architecture as Saint. Describing it in full is beyond the scope of this document, but put simply in an MVC system all user input is handled by something called the &ldquo;controller&rdquo;. This is, generally and specifically in the case of Saint, accomplished using an object. All controller classes are located inside the &ldquo;code/Controller&rdquo; directory. Controllers are subject to the same loading rules as blocks and styles. Thus, in the case of a file existing in both &ldquo;code/Controller&rdquo; and &ldquo;core/code/Controller&rdquo; the user version will be preferred over the core version.</div>

<div class="standard"><a id='magicparlabel-186'></a>
First you must create a class in the &ldquo;Controller&rdquo; directory. The name of the class must match the name of the file in the form of &ldquo;Saint_Controller_Filename&rdquo; with no extension. It is case sensitive. Then, add methods to your class which accept your expected input as arguments.</div>

<div class="standard"><a id='magicparlabel-187'></a>
In Saint, the &ldquo;Page&rdquo; controller handles page requests. It acts as a router for input, checking the GET and POST variables and calling the appropriate functions located in other controllers. Copy this file from the core directory to your theme code directory before making any changes. Then, using the other code as an example, create a hook to call your own controller's function.</div>
<h3 class="subsection"><span class="subsection_label">6.1</span> <a id='magicparlabel-188'></a>
Controller Security</h3>
<div class="standard"><a id='magicparlabel-189'></a>
Security in Saint is handled in two main places. As the controllers are specifically meant to take action in response to user input they are, in essence, the &ldquo;users&rdquo; of our website from the system's perspective. They ask the models to manipulate the data. Models trust the controller's intent inherently. Thus, we need to ensure that our controllers only allow appropriate action to be taken by users. Any time you program a controller to perform actions be sure you also use the functions listed in the &ldquo;Permissions&rdquo; section and add a check on whether or not the user is allowed to perform the action requested.</div>

<div class="standard"><a id='magicparlabel-190'></a>
Controllers handles all of the &ldquo;action&rdquo; type security checks. The other half of Saint security is handled by the models themselves and is outlined in the following section.</div>
<h2 class="section"><span class="section_label">7</span> <a id='magicparlabel-191'></a>
Models</h2>
<div class="standard"><a id='magicparlabel-192'></a>
Access and manipulation of non-volatile data sources in a MVC system like Saint is the domain of the models. Located in the &ldquo;code/Model&rdquo; directory, models are classes that interact with the database. To the rest of the system, a model is representative of an abstract data type. You may view the core model source files and get an idea of how this works, but the general idea is that instead of accessing the data directly from other places you instead call a function offered by the model and use the result. Similarly, instead of changing the data you pass it to the model and have the model do the manipulation.</div>
<h3 class="subsection"><span class="subsection_label">7.1</span> <a id='magicparlabel-193'></a>
Model Security</h3>
<div class="standard"><a id='magicparlabel-194'></a>
Protection against straightforward attempts by users to do that which is not allowed in the group access listing is provided by your controllers. However, a second type of attack exists whereby malicious users attempt to trick the system into running a different command than is originally intended. In a PHP/MySQL environment, that type of attack is centered on SQL injection and filesystem manipulation. Any time a model is to access the filesystem or the database using input passed to it the input must first be passed through the Saint sanitization function. This function will return the sanitized string, or false if it fails the test. It accepts a regular expression to match as an optional second argument; several of these are defined in the core Saint config file as constants and used throughout the site. Here is an example using the function:</div>

<div class="standard"><a id='magicparlabel-195'></a>
</div>
<div class='float float-listings'><pre>if ($sname = Saint::sanitize($name,SAINT_REG_NAME)) {
	// Do things here with $sname
} else {
	Saint::logError("Name '$name' is invalid.",__FILE__,__LINE__);
}</pre></div>

<h4 class="subsubsection"><span class="subsubsection_label">7.1.1</span> <a id='magicparlabel-204'></a>
Patterns</h4>
<div class="standard"><a id='magicparlabel-205'></a>
All names used within the Saint system must pass the built in SAINT_REG_NAME pattern. There are various reasons for this, but the one that is most likely to make people curious will be the restriction on underscores. To put it as simply as possible, Saint names are meant to match the file hierarchy exactly. As such they need to use forward slashes. However, there are limited characters available for use in standards compliant web IDs and slashes aren't one of them. Saint changes all slashes into underscores for web use via the Saint::convertNameToWeb($sname) function, and back using Saint::convertNameFromWeb($sname).</div>

<div class="standard"><a id='magicparlabel-206'></a>
There are four basic patterns defined in the core config file and used through Saint. These are SAINT_REG_NAME, SAINT_REG_ID, SAINT_REG_EMAIL, and SAINT_REG_BOOL. The ID pattern matches positive integers only. The boolean pattern matches 0 or 1, not &ldquo;true&rdquo; or &ldquo;false&rdquo; (the PHP constants TRUE and FALSE will work, since they represent 1 and 0 respectively). The e-mail pattern will match any valid e-mails. You are free to add your own patterns to the user config file to use in your code, or to use the built in ones available.</div>
<h2 class="section"><span class="section_label">8</span> <a id='magicparlabel-207'></a>
Logging</h2>
<div class="standard"><a id='magicparlabel-208'></a>
There are three log types in Saint: events, warnings, and errors. They each append new entries to their respective files, while all entries show up in the administrator's web interface log. All of the functions accept the log message as the first argument as well as optional second and third arguments representing the file and line numbers of the code, respectively. Logging can be set to exclude events, both events and warnings, or to be completely disabled by setting SAINT_LOG_LEVEL in the core config file.</div>
<h3 class="subsection"><span class="subsection_label">8.1</span> <a id='magicparlabel-209'></a>
Events</h3>
<div class="standard"><a id='magicparlabel-210'></a>
Events are fired in the normal course of activity. They are used to indicate that an activity has occurred. An example of this would be an event firing when saving user data:</div>

<div class="standard"><a id='magicparlabel-211'></a>
</div>
<div class='float float-listings'><pre>Saint::logEvent("Saved info for user '$this-&gt;_username'.");</pre></div>

<h3 class="subsection"><span class="subsection_label">8.2</span> <a id='magicparlabel-216'></a>
Warnings</h3>
<div class="standard"><a id='magicparlabel-217'></a>
Warnings indicate problems that are not fatal to the running of the program. An example of this would be the warning that is logged when Saint can't find a style that is indicated in one of the templates:</div>

<div class="standard"><a id='magicparlabel-218'></a>
</div>
<div class='float float-listings'><pre>Saint::logWarning("Cannot find style $style.");</pre></div>

<h3 class="subsection"><span class="subsection_label">8.3</span> <a id='magicparlabel-223'></a>
Errors</h3>
<div class="standard"><a id='magicparlabel-224'></a>
Errors are problems that stop the function from completing a requested action. This indicates problems that should be fixed as soon as possible. If you see errors that you think are bugs then submit a full report with as many details as possible to bugs@saintcms.com. An example of error log usage is found below:</div>

<div class="standard"><a id='magicparlabel-225'></a>
</div>
<div class='float float-listings'><pre>Saint::logError("Invalid file name: '$name'. ",__FILE__,__LINE__);</pre></div>


<div class="standard"><a id='magicparlabel-230'></a>
For those who are wondering, __FILE__ and __LINE__ are magic constants provided by PHP which return the current file name and current line number at the point of their use. They must be passed to the logging function since running them inside returns the file and line of the log function.</div>
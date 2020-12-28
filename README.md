# gCMS - Git based CMS
As the header described - this is CMS where content you would like to serve is stored under Git repository.

## Idea behind
For a while I was thinking about paying back to the whole IT world. Not only for offering me the limitless source of knowledge called Internet. But foremost to say thankyou to all great people that contribute to this source. This is why I wanted to start a blog/page (call it however you want) where I can share what I have learnt - so I can share it with others - mainly new in IT business.
I did few rounds to this topic. First with Wordpress (great thing, used it in many projects), then with static pages. As you can see I jumped back and forth looking for solution that will suits me (mostly my laziness). But it always was to complicated, to consuming or to boring to use it or lacking of necessary functionalities. And if everything was ok - I found it was to expensive (big hello to Confluence guys).

One day - exactly 7/11/2020 (for those from USA d/m/yyyy) - I figured out that publishing content to the web is in its fundamentals nothing else like doing new feature in the code. So why not to create content in favorite editor/IDE and when it is finished commit it to the repository?

This was this big moment - use Git as content storage.

## How does it works?
gCMS is a bunch of scripts that reads selected input folder for particular file types (*.page.md). Each page

Before I jump into describing it, you need to now my constraints.

### Constraints
* Content first approach - I like to focus on creating content. I don't want to spend time on figuring out how to add beautifully code snippet. This is why GitHub flavored Markdown will be used for content.
* Follow the WVD process: Write, Verify, Deploy:
    * Write content, by creating appropriate markup described documents;
    * Create pull/merge request, do the code review and if everything is ok - accept it;
    * Deploy content by building bunch of static html files that you can upload to almost any web server.

The last one can be triggered from command line or by web hook triggered by your repository. From now on, process of building static pages will be called "Build".

### How to start guide
Follow below guide to start with your first simple blog based on gCMS.

#### Step 1: Preparation
As gCMS is not curently
Create new folder "blog" and start new composer project
```bash
composer init
```
```bash
mkdir -p blog/{content,output,templates}
```
* Create configuration file blog/config.json \
```json
{
    "input": {
        "type": "folder",
        "path": "blog/content"
    },
    "output": {
        "path": "blog/output",
        "static": "/static",
        "relative": false,
        "extension": '.html'
    },
    "resources": {
        "templates": "blog/templates",
        "static": "blog/templates/static"
    },
    "name": "Best blog",
    "slogan": "Get it done - quickly with gCMS!"
}
```
* Create appropriate twig templates and place them in blog/templates directory (or you can use examples provided with gCMS - !!!eye blindness guaranteed!!!)
* Create new file and name it whatever you want - but add .page.md extension to it e.g. main.page.md, how-to-display-logs-in-linux-systems.page.md; \
For the purpose of this instruction we will name it sample.page.md;
* Add content to this file like in example below. First JSON code block holds page configuration. After it you can place actual content. You can use Markup syntax (currently GitHub markup is supported): \
```
    ```json
    {
        "slug": "pages/first-page-slug",
        "title": "My first page in gCMS",
        "lang": "en",
        "tags": ["learning","gCMS"],
        "categories": ["HowTo"],
        "excerpt": "Perfect page excerpt. *Rich* in _content_. With *great* formatting",
        "author": "Adam Wojciechowski"
        "createDate": 1608069061,
        "menuItemNumber": -1
    }
    ```
    ## This is page content
    You can write whatever you want!
    ```php
    <?php

    echo 'Even code samples';
    ```
```
* Trigger build process: \
```bash
.\gcms build -c blog/config.json
```

And thats it! Now you have your blogs' content generated into static files located in blog/output.

## Details
### How to install gCMS
Depending on what you need you can use many different repositories, e.g. one for content and second one for templates and configs. This way you can have one repository with gCMS and your templates and many others just for content. In such case |I recommend to keep configuration files in same repository where you put your templates.

To add gCMS to your project just use composer:
```bash
composer require webee-online/g-cms
```

### CMS configuration options description
Long story short below you will find description of all configuration options you can use:
* input:
** type - type of the source of the content:
*** 'git' - source folder is a git repository (update method works only if this is set)
*** 'folder' - source folder is not a git repository

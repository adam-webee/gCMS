# gCMS - Git based CMS
As the header described - this is CMS where content you would like to serve is stored under Git repository.

## Idea behind
For a while I was thinking about paying back to the IT world. Not only for offering me the limitless source of knowledge called Internet. But foremost to say "thank you" to all great people that contribute to this source. This is why I wanted to start a blog/page (call it however you want) where I can share what I have learnt - so I can share it with others - mainly with new in IT business.
I did few rounds to this topic. First with Wordpress (great thing, used it in many projects), then with static pages. As you can see I jumped back and forth looking for solution that will suits me (face the truth - I'm a lazy bastard). But it always was to complicated, to consuming or to boring to use it or lacking of necessary functionalities. And if everything was ok - I found it was to expensive (big hello to Atlassian guys ;)).

One day - exactly 7/11/2020 (for those from USA d/m/yyyy) - I figured out that publishing content to the web is in its fundamentals nothing else like doing new feature in the code. So why not to create content in favorite editor/IDE and when it is finished commit it to the repository?

This was this big moment - use Git as content storage.

## How does it works?
gCMS is a tool that reads selected input folder for particular file types (*.page.md). For each of them it will create static html (or other) page according to defined templates. Such static files can be uploaded to any server that can host html files (simple Apache host will do the trick).

Before I jump into describing it, you need to now my assumptions.

### Assumptions
* Content first approach - I like to focus on creating content. I don't want to spend time on figuring out how to add beautifully code snippet. This is why GitHub Flavored Markdown will be used for content (as a bonus I can see my formatted content straight on github web page).
* Follow the WVD process: Write, Verify, Deploy:
    * Write content, by creating appropriate markup described documents;
    * Create pull/merge request, do the code review and if everything is ok - accept it;
    * Deploy content by building bunch of static html files that you can upload to almost any web server.

The last one can be triggered by the Cron job or by web hook triggered by your repository. From now on, process of building static pages will be called "Build".

### How to start guide
Follow below guide to start with your first simple blog based on gCMS.

#### Step 1: Preparation
```bash
# Create new folder "blog" and necessary sub folders
mkdir -p blog/{content,output,templates} && cd blog

# Initialize new composer project - follow the guide
composer init

# Add gCMS to your project if you didn't do it during initialization
composer require webee-online/g-cms

# Install dependencies
composer install

# As a start point you can use templates and content I prepared for demo - but be warned - eye pain guaranteed!!!
cp -r vendor/webee-online/g-cms/example/blog/{content,templates} ./
```
#### Step 2: Configuration
Create configuration file "blog/config.test.json".
```bash
vim config.test.json
```
And insert below JSON into it:
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
        "extension": ".html"
    },
    "resources": {
        "templates": "blog/templates",
        "static": "blog/templates/static"
    },
    "name": "Best blog",
    "slogan": "Get it done - quickly with gCMS!"
}
```

#### Step 3: Content development
Create new file and name it whatever you want - but add *.page.md* extension to it e.g. *main.page.md*, *how-to-display-logs-in-linux-systems.page.md*;
For the purpose of this guide we will name it hello-world.page.md;
```bash
vim content/hello-world.page.md
```
Now add content to this file like in example below. First JSON code block holds page configuration. After it you can place actual content. You can use GitHub Flavored Markup syntax:
```
    ```json
    {
        "slug": "pages/hello-world",
        "title": "My first Hello World Page in gCMS",
        "lang": "en",
        "tags": ["learning","gCMS", "Hello World"],
        "categories": ["HowTo"],
        "excerpt": "Perfect page excerpt. *Rich* in _content_. With *great* formatting",
        "author": "Adam Wojciechowski"
        "createDate": 1608069061,
        "menuItemNumber": -1
    }
    ```
    ## This is page content
    You can write whatever you want using GitHub Flavored Markup syntax!
    ```php
    <?php

    echo 'Even code samples';
    ```
```

#### Step 4: Generate content
Trigger build process:
```bash
.\gcms build -c config.json
```

And thats it! Now you have your blog content generated into static files located in blog/output directory.

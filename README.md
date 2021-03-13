# gCMS - Git-based CMS
As the header described - this is CMS where the content you would like to serve is stored under the Git repository.

## Idea behind
For a while, I was thinking about paying back to the IT world. Not only for offering me the limitless source of knowledge called the Internet. But foremost to say "thank you" to all great people that contribute to it. This is why I wanted to start a blog/page (call it however you want). The place where I can share what I have learned with others. Mainly with new in the IT business.
I did few rounds on this topic. First with WordPress (a great thing, used it in many projects). Then with static pages. As you can see I jumped back and forth looking for a solution that will suit me. Face the truth - I'm a lazy bastard. But it always was too complicated, too consuming, or too boring to use, or lacking necessary functionalities. And if everything was ok - I found it was too expensive (a big hello to Atlassian guys ;)).

One day - exactly 7/11/2020 (for those from USA d/m/yyyy) - I figured out that publishing content to the web is in its fundamentals nothing else like doing a new feature in the code. So why not create content in favorite editor/IDE and when it is finished commit it to the repository?

This was this big moment - use Git as content storage.

## How does it work?
gCMS is a tool that reads selected input folder for particular file types (e.g.: *.page.md, *.category.md, etc.). For each of them, it will create a static HTML (or other) page according to defined templates. Such static files can be uploaded to any server that can host HTML files (a simple Apache host will do the trick or cloud storage like S3).

Before I jump into describing it, you need to know my assumptions.

### Assumptions
* Content first approach - I like to focus on creating content. I don't want to spend time figuring out how to add beautifully code snippets. This is why GitHub Flavored Markdown will be used for content (as a bonus I can see my formatted content straight on the GitHub web page).
* Follow the WVD process: Write, Verify, Deploy:
    * Write content, by creating appropriate markup described documents;
    * Create pull/merge request, do the code review and if everything is ok - accept it;
    * Deploy content by building a bunch of static HTML files that you can upload to almost any web server.

The last one can be triggered by the Cron job or by a webhook triggered from your repository. Starting now, the process of building static pages will be called "Build".

### How to start guide
Follow this guide to start with your first simple blog based on gCMS.

#### Step 1: Preparation
```bash
# Create new folder "blog" and necessary sub folders
mkdir -p blog/{content,output,templates} && cd blog

# Initialize new composer project - follow on-screen instructions
composer init

# Add gCMS to your project if you didn't do it during the initialization
composer require webee-online/g-cms

# Install dependencies
composer install

# As a start point you can use templates and content I prepared for a demo - but be warned - eye pain guaranteed!!!
cp -r vendor/webee-online/g-cms/example/blog/{content,templates} ./
```
#### Step 2: Configuration
Create configuration file "blog/config.test.json".
```bash
vim config.test.json
```
And insert the following JSON into it:
```json
{
    "input": {
        "type": "folder",
        "path": "blog",
        "contentFolder": "/content"
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

#### Step 3a: Content development
Create new file and name it whatever you want - but add *.page.md* extension to it e.g. *main.page.md*, *how-to-display-logs-in-linux-systems.page.md*;
For the purpose of this guide we will name it hello-world.page.md;
```bash
vim content/hello-world.page.md
```
Now add content to this file like in the example below. JSON code block holds page configuration and is mandatory. Place actual content after it. You can use GitHub Flavored Markup syntax:
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

### Step 3b: Category page
If you like to add a category listing page to your blog - just create a new file. Name it whatever you want - but add *.category.md* extension to it, e.g. *blog.category.md*.
For the purpose of this guide we will name it blog.category.md;
```bash
vim content/blog.category.md
```
Content for category file is much simpler than for page file, and below is an example:
```
    ```json
    {
        "slug": "categories",
        "title": "Categories",
        "lang": "en",
        "menuItemNumber": 1
    }
    ```
```

### Step 3c: Main page
You might need also a start page. Do it in the same way as the category page but change the extension to *.mainpage.md*, e.g. *blog.mainpage.md*.
For this guide we will create main page named blog.mainpage.md;
```bash
vim content/blog.mainpage.md
```
Main page file content might be like this:
```
    ```json
    {
        "slug": "index",
        "title": "Home",
        "lang": "en",
        "menuItemNumber": 0
    }
    ```
```

#### Step 4: Generate content
Trigger build process:
```bash
.\gcms build -c config.json
```

And that's it! Now you have your blog content generated into static files located in blog/output directory.

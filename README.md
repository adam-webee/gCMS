# gCMS - Git based CMS
As the header described - this is CMS where content you would like to serve is stored under Git repository.

## Idea behind
For a while I was thinking about paying back to the whole IT world, but not only, for offering me limitless source of knowledge called Internet. And of course to all great people that contribute to this source. This is why I wanted to start a blog/page (call it however you want) where I can share what I have learnt - so others new in business can use it.
I did few rounds to this topic. First with Wordpress (great thing, used it in many projects), then with static pages. As you can see I jumped back and forth looking for solution that will suits me (mostly my laziness). But it always was to complicated, to consuming or to boring to use it or lacking of necessary functionalities. And if everything was ok - I found it was to expensive (big hello to Confluence guys).

One day - exactly 7/11/2020 (for those from USA d/m/yyyy) - I figured out that publishing content to the web is in its fundamentals nothing else like doing new feature in the code. So why not to create content in favorite editor/IDE and when it is finished commit it to the repository?

This was this big moment - use Git as content storage.

## How does it works?
Before I jump into describing it, you need to now my constraints.

### Constraints
* Content first approach - I like to focus on creating content not on figuring out how to add beautifully code snippet. This is why Markdown will be used for content.
* Code, Verify, Deploy:
    * Develop content, by creating appropriate page.md or post.md documents.
    * Create pull/merge request, do the code review and if everything is ok - accept it.
    * Deploy content by building bunch of static html files that you can upload to almost any web server

The last one can be triggered from command line or by web hook triggered by your repository. From now on, process of building static pages will be called "Build".

### How to use it
* Create new, empty file and name it whatever you want - but add .page extension to it e.g. main.page, how-to-display-logs-in-linux-systems.page;
* Add content to this file using Markup syntax (described below);
* Add at the top of the file JSON structure with page preferences like: slug, key words, tags, categories etc. (detailed description of this structure is somewhere below)
* Add and commit this file to the repository.
* Trigger build and deploy process

And have new perfect content on your web page.

## Details

### How to install and configure gCMS
#### Installation step by step guide
#### Configuration step by step guide

### How to create first page
#### No review necessary
```bash
cd /path/to/content/repository
git checkout deploy-branch-name

vim my-first.page
```
Now add your content:
1. Define page properties:
```json
{
    "title": "This is my first page",
    "slug": "im-number-one",
    "categories": [
        "Mian category",
        "Additional category1", "Other category 2"
    ],
    "tags": [
        "tutorial", "how to", "step by step"
    ],
    "media": [
        "IMAGE-XXX": "kitten.jpg",
    ]
}
```
2. Define content:
```
This is page content.
## Any valid markdown
Can be used to create this content
```
3. Now commit page and all related media files.
4. Trigger build and deploy process
#### Review is necessary
Just create content on other branch and create pull/merge request. Rest of the steps are exactly the same.

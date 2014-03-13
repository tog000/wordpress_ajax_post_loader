
Wordpress Ajax Post Loader
==========================

Dependencies
--------------------------
- [jQuery 1.8](http://jquery.com/ "jQuery 1.8")
- [Isotope](https://github.com/desandro/isotope "Isotope")

Limitations
--------------------------
There are hardcoded values related to pagination, spinners for progress
and other values related to the original application for which this
was used

Usage
--------------------------

The following JavaScript code will fetch the data and 
replace the values in the template with the contents of the post.

```js
// Ajax news items
// @param template is a script type "text/template"
// @param paginator is a DOM element that contains the pagination indicators
// @param target is a DOM element to populate
// @param page is the page number
// @param posts is th enumber of posts to display
// @param category the category to filter by (optional)
// @param callback function to execute after reLayout
function loadNewsAjax(template, zone, paginator, target, page, posts, category, callback){
	var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

	jQuery(paginator).find(".spinner").fadeIn()

	var oldElements = jQuery(target).find(".portfolio-item");
	oldElements.addClass("desaturate").css("opacity",0.5);

	jQuery.get(ajaxURL,{
				action:"ajax-post-loader",
				page:page,
				posts:posts,
				category: category,
				zone: zone
			},

			function(response){

				newElements = "";

				for(post_index in response.posts){

					post = response.posts[post_index];
					
					var templateHTML = jQuery(template).html();

					templateHTML=templateHTML.replace(/{%permalink%}/g,	post.permalink);
					templateHTML=templateHTML.replace(/{%news_kind%}/g,	post.news_kind);
					templateHTML=templateHTML.replace(/{%title%}/g,		post.post_title);
					templateHTML=templateHTML.replace(/{%thumbnail_alt%}/g,	post.post_name);
					templateHTML=templateHTML.replace(/{%thumbnail%}/g,	post.thumbnail);
					templateHTML=templateHTML.replace(/{%excerpt%}/g,	post.post_excerpt);
					templateHTML=templateHTML.replace(/{%categories%}/g,	post.categories);
					templateHTML=templateHTML.replace(/{%comment_count%}/g,	post.comment_count);
					comment_text = "Comments"
					if(post.comment_count == 1){
						comment_text = "Comment"
					}

					// Build date
					d = new Date(post.post_date.replace(" ","T")); //Fix for Firefox
					day = d.getDate()
					suffix = (day%10 == 1 && (day<10 || day > 20) )? "st":(day%10 == 2 && day!=12)?"nd":(day == 3 && day!=23)?"rd":"th";
					month=monthNames[d.getMonth()]

					templateHTML=templateHTML.replace(/{%comments%}/g,	"<a href=\""+post.comments_link+"\" title=\"Comment on "+post.post_title+"\">"+post.comment_count+" "+comment_text+"</a>");


					templateHTML=templateHTML.replace(/{%date%}/g,	month+" "+d.getDate()+suffix+", "+d.getFullYear());

					newElements+=templateHTML;

				}

			jQuery(target).isotope( 'remove', oldElements );
			oldElements.remove();
			jQuery(target).isotope( 'insert', jQuery(newElements));

			var paginationIndicator = jQuery(paginator);
			paginationIndicator.find("input").val(response.current_page);
			paginationIndicator.find("#total").text(response.total_pages);

			callback.call(this,response);

			jQuery(paginator).find(".spinner").fadeOut()

		},"json");
}
```
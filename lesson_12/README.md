## Lesson 01

- Blank HTML page that displays a json file as text only.
- Changed the title tag.
- And added folder `css` with a tiny external css file.

## Lesson 02

- request `colors.json` file from server and display in console.
- here is our function:

```js
/**
* theUrl [string] : url resource needed
* syncMethod [bool] : implies asynchronous or synchronous
*/
function httpGet(theUrl,syncMethod) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", theUrl, syncMethod); // false for synchronous request
    xmlHttp.send(null);                // true for asynchronous
    return xmlHttp.responseText;
}
```

- Notice I added sync method in the params.
- This is because in class I did NOT notice it defaulted to synchronous (meaning wait for response) 
- In `index.html` change `false` to `true` and see what happens.

## Lesson 03

We need to wait for the DOM to load before trying to fill a div with data. Ensuring things happen when you expect them to is just something we will have to deal with in JS. That is why frameworks are so appealing. They handle a lot of the ordering issues for you.

Below is a link to a stack overflow response to similar question. The accepted answer didn't work for me so I found a later response. However, the accepted response does have a good explanation of what a framework like jquery does to have a more robust yet involved solution.

https://stackoverflow.com/questions/9899372/pure-javascript-equivalent-of-jquerys-ready-how-to-call-a-function-when-t

The answer I picked:

```js
document.addEventListener("DOMContentLoaded", function(event) {
    // Your code to run since DOM is loaded and ready
});
```

## Lesson 04

So we still did not get our data did we?!?! What is the problem? Lets look at the code:

```js
    document.addEventListener("DOMContentLoaded", function(event) {
      console.log("dom loaded")
      var dataDiv = document.getElementById("content");      
      var data = httpGet('http://terrywgriffin.com/lessons/lesson_03/colors.json',true)
      console.log(data); 
      dataDiv.innerHTML = data;
    });
```
1. The DOM is loaded, that's not the issue.
2. Getting the element reference is not the issue.
3. Running the `httpGet` as asyncronous is not really the issue either (maybe a little).
4. The issue is the last line and how it relates to the `httpGet`

Javascript does not care if our `httpGet` is not finished! It still tries to assign the `data` variable to the `innerHTML` of our div. The problem is that the `data` will NEVER be ready using this procedural top down approach. We need a new syntax to ensure our data is back. For this we will use a `callback`.

What is a callback:

```js
/**
* url [string] : string url to get data from
* handledata [function] : we are passing an entire function into this bad boy
*/
function doSomthing(url, handledata) {
  console.log(`Trying to get data from ${url}.`);
  handledata();
}

getSomeData('http://data.com/cool/data.json', function() {
  console.log('Got my data!');
});
```

Its a little more complicated for a GET request, but its still the same concept:

https://stackoverflow.com/questions/5485495/how-can-i-take-advantage-of-callback-functions-for-asynchronous-xmlhttprequest

Old httpGet:
```js
function httpGet(theUrl, syncMethod) {
    console.log('httpget')
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", theUrl, syncMethod); // false for synchronous request
    xmlHttp.send(null);                // true for asynchronous
    return xmlHttp.responseText;
}
```

New httpGet:
```js
function httpGet(theUrl, syncMethod) {
    console.log('httpget')
    var xmlHttp = new XMLHttpRequest();
    // Built in method to handle a "completed" GET request
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            callback(xmlHttp.responseText); // Send responseText to callback!
        }
    };
    xmlHttp.open("GET", theUrl, syncMethod); // false for synchronous request
    xmlHttp.send(null);                // true for asynchronous
    return xmlHttp.responseText;
}
```

## Lesson 05

Lets use jQuery as a framework. We can very much simplify our GET request if we use a framework. We need to include jquery (I downloaded jquery and saved it in a `js` folder):

```js 
<script src="js/jquery-3.4.1.min.js"></script>
```

Then, here is code needed to perform a GET request (cool thing is this response is already parsed as JSON):

```js 
$(function() {
    $.get("http://terrywgriffin.com/lessons/lesson_05/colors.json")
    .done(function (data) {
        console.log(data);
    });
});
```

## Lesson 06

Here we simply loop through the object returned from the server, and print out each item. Remember the JSON structure:

```json
[
    {
        "html": "#4b0082",
        "name": "indigo",
        "rgb": [
            75,
            0,
            130
        ]
    },
    {
        "html": "#ffd700",
        "name": "gold",
        "rgb": [
            255,
            215,
            0
        ]
    }
]
```

This is an array of 2 colors where the original file has 148. Actually, it is an array of objects, where each object consists of 3 key value pairs:

1. html : hex value
2. name : human readable string name
3. rgb : array of [R, G, B]

So, this loop processed the array of objects printing out each value 1 per line:

```js
    // Loop through result array
    for(var i=0;i<data.length;i++){
        // print name and html values
        $("#content").append(data[i].name + ' , ' + data[i].html + ' , ') ;
        // since rgb is array of 3, loop and print
        for(var j=0;j<3;j++){
            $("#content").append(data[i].rgb[j]) ;
        }
        // new line
        $("#content").append("<br>");
    }
```

## Lesson 07

Lets "sort" the json object by name before dumping it to the screen:

```js
// https://stackoverflow.com/questions/979256/sorting-an-array-of-javascript-objects-by-property/979325#979325

// Remember functions can be assigned to variables
// And functions can be passed in as params (primer for example)
var sort_by = function (field, reverse, primer) {

    // ternary statement saying if primer exists:
    // key = primer(value) else
    // key = value
    var key = primer ?
        function (x) { return primer(x[field]) } :
        function (x) { return x[field] };

    reverse = !reverse ? 1 : -1;

    // Function bodys are returned and / or passed around just like variables
    // This returns a function already setup using the above code because 
    // the "sort" method on arrays is OK with passing a function to it for specific 
    // sorting.
    return function (a, b) {
        return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
    }
}

// usage:

// if data where a resulting json object
// then sorting it allows us to pass a function
// into the sort method (and sort_by returns such a function)
data.sort(sort_by('name', false));
```

## Lesson 08

We haven't talked about tables, sooo lets use a table to format our tabular data. A table typically consists of Rows and Columns with a Header (or label) at the top of each column. There are also attributes that allow you to tailor your table (padding for cells, border colors and thickness, background colors and others). Tables can be tailored again using spans (rowspan, colspan) allowing data to traverse multiple columns or rows. Having said all of this, tables were shunned for a while. For tabular data they are "acceptable" but many developers used to use them as screen organizers, and this became very frowned upon. With modern CSS and Grid layouts, we don't need tables for positioning, just tabular data.

**Example Table:**

https://developer.mozilla.org/en-US/docs/Web/HTML/Element/table
```html
<table>
    <thead>
        <tr>
            <th colspan="2">The table header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>The table body</td>
            <td>with two columns</td>
        </tr>
    </tbody>
</table>
```


Initially I used this jQuery to add my tabular data to the page:
```js
$("#content").append('<table>');
for (var i = 0; i < data.length; i++) {
    $("#content").append('<tr><td>'+data[i].name + '</td><td>' + data[i].html + '</td>');
    for (var j = 0; j < 3; j++) {
        $("#content").append('<td>'+data[i].rgb[j] + '</td>');
    }
    $("#content").append('</tr>');
}
$("#content").append('</table>');
```
It didn't work out so well. Look at the actual output and see if you can deduce why.

I ended up writing 2 functions to display both a correct solution and the in-correct solution. See that actual html file for the code.

## Lesson 09

Lets add styling to our table!

We have used jQuery in the previous examples. I hope you have noticed that the syntax to "select" a DOM element is quicker using the jQuery framework. 

javascript:
```js
document.getElementById("content").innerHTML = something;
```

jQuery:
```js
$("#content").html(something);
```

The whole concept of "selectors" can get pretty powerful, so I will cover only major selectors now:

jQuery:
```js
// Change inner html for some "tag" or "element with an id" or a "class":
$("sometag").html(stuff);
$("#someid").html(stuff);
$(".someclass").html(stuff);

```
jQuery uses the `.` (dot) and the `#` (hash) mirroring css when it is trying to access a "class" or an element with an "id". If you do not use the dot or hash, you can "select" an entire tag. 

There are many more selector functions besides "html" to manipulate the DOM with. Here are a couple. 
```js
// alter the body css to have a new bg color
$( "body" ).css( "background-color", "#C0C0C0" );

// append data to an element with id "content"
$( "#content").append(data)
```

In fact, this line will make all tables have alternating stripes:

```js
$( "tr:odd" ).css( "background-color", "#bbbbff" );
```

## Lesson 10

In this lesson we are simply adding an extra column to our table and coloring the background of each rows new cell using the existing JSON data's `html` value for that row.

One possibility is to color the entire row background color and then choose text color based on some function. The function below is using ES2015 syntax:

```js
const hexToLuma = (colour) => {
    const hex   = colour.replace(/#/, '');
    const r     = parseInt(hex.substr(0, 2), 16);
    const g     = parseInt(hex.substr(2, 2), 16);
    const b     = parseInt(hex.substr(4, 2), 16);

    return [
        0.299 * r,
        0.587 * g,
        0.114 * b
    ].reduce((a, b) => a + b) / 255;
};
```
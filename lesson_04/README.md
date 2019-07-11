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

- STILL should NOT work as is

## Lesson 04

So Lesson 03 still did not get our data did it?!?! What is the problem? Lets look at the code:

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
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

We need to wait for the DOM to load before trying to fill a div with data. Ensuring things happen when you expect them to, is just something we will have to deal with in JS. That is why frameworks are so appealing. They handle a lot of the ordering issues for you.

Below is a link to a stack overflow response to similar question. The accepted answer didn't work for me so I found a later response. However, the accepted response does have a good explanation of what a framework like jquery does to have a more robust yet involved solution.

https://stackoverflow.com/questions/9899372/pure-javascript-equivalent-of-jquerys-ready-how-to-call-a-function-when-t

The answer I picked:

```js
document.addEventListener("DOMContentLoaded", function(event) {
    // Your code to run since DOM is loaded and ready
});
```
## Lesson 01

- Blank HTML page that displays a json file as text only.
- Changed the title tag.
- And added folder `css` with a tiny external css file.

## Lesson 02

- request `colors.json` file from server and display in console.
- should NOT work as is
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
- Also inspect the page (and see the "synchronous" error?)
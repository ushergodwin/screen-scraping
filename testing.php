<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Index</title>
    <!-- Bootstrap CSS file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div id="root">

</div>
<!-- JS files: jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script>
    let container = document.createElement('div');
        container.classList.add("container");
    let row = document.createElement('div');
        row.classList.add("row", "justify-content-center")
    let column = document.createElement("div");
        column.classList.add("col-md-12", "col-lg-6", "col-xl-6")
    let button = document.createElement("button");
        button.classList.add("btn", "btn-info");
        let buttonText = document.createTextNode("CLICK TO SCRAP")
        button.setAttribute("id", "scraping-btn")
        button.appendChild(buttonText);
    column.appendChild(button);
    row.appendChild(column);
    container.appendChild(row);
    const root = document.getElementById('root');
    root.appendChild(container);
    button.addEventListener("click", function(){
        let request = new XMLHttpRequest();
        const text = document.createTextNode("scraping...");
        column.appendChild(text);
        request.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                //change this location to your view file
                window.location.href = "screen-scraping.php";
            }
        }
        request.open("GET", "screen_scraping.php?start_scraping=true");
        request.send();
    });

</script>
</body>
</html>
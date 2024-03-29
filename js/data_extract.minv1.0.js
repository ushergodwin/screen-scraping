'use strict';
function container(){
    var el = document.createElement('div');
    el.classList.add('container-fluid', 'mt-3');
    el.appendChild(row());
    return el;
}

function row() {
    var el = document.createElement('div');
    el.classList.add('row');
    el.appendChild(column1());
    el.appendChild(column2());
    return el;
}

function column1() {
    var col = document.createElement('div');
    col.classList.add('col-md-12', 'col-lg-7', 'col-xl-7');
    var btn  = document.createElement('button');
    btn.classList.add('btn', 'btn-primary');
    btn.setAttribute('id', 'scraping-btn');
    var btnText = document.createTextNode('CLICK TO START SCRAPING  ');
    btn.appendChild(btnText);
    btn.addEventListener('click', scrap);

    var span = document.createElement('span');
    span.classList.add('spinner-border', 'spinner-border-sm', 'text-light', 'd-none');
    span.setAttribute('id', 'start-scraping');
    btn.appendChild(span);


    var response = document.createElement('div');
    response.setAttribute('id', 'response');
    response.classList.add('mt-3');
    col.appendChild(btn);
    col.appendChild(response);
    return col;
}

function column2() {
    var col = document.createElement('div');
    col.classList.add('col-md-12', 'col-lg-5', 'col-xl-5');
    var btn  = document.createElement('button');
    btn.classList.add('btn', 'btn-primary');
    btn.setAttribute('id', 'stat-btn');
    var btnText = document.createTextNode('GET STATISTICS');
    btn.appendChild(btnText);
    btn.setAttribute('disabled', true);
    btn.addEventListener('click', getStatistics);

    var response = document.createElement('div');
    response.setAttribute('id', 'stat-response');
    response.classList.add('mt-3');
    col.appendChild(btn);
    col.appendChild(response);
    return col;
}

window.onload = () => {
    var main = document.getElementById('main');
    main.appendChild(container());

}
if (document.getElementById('scraping-btn')) {
    var ajaxBtn = document.getElementById('scraping-btn');
    ajaxBtn.addEventListener('click', function() {
        scrap()
    })
}
function scrap() {
    $(document).ready(function() {
        $.ajax({
            url: '../models/ScrapingModel.php',
            type: 'get',
            data: {get:true},
            beforeSend: ()=> {
                $("#scraping-btn").attr('disabled', true);
                $("#response").html("<span class='spinner-border spinner-border-sm text-info'></span> scraping...")
            },
            success: (data) => {
                $("#response").html("<i class='fas fa-check-circle text-success'> </i> scraping complete <br/> <span class='spinner-border spinner-border-sm text-info'></span> Fetching data...");
                setTimeout(() => {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (this.readyState === 4 && this.status === 200) {
                            document.getElementById('response').innerHTML =  this.responseText;
                        }
                    }
                    xhr.open('get', '../models/ScrapingModel.php?table=true');
                    xhr.send();
                }, 1000);
            },
            complete: ()=> {
                $("#scraping-btn").attr('disabled', false);
                $("#stat-btn").attr('disabled', false);
            }
        });
    });  
}

function getStatistics() {
    $(document).ready(function() {
        $.ajax({
            url: '../models/ScrapingModel.php',
            type: 'get',
            data: {get_stat:true},
            beforeSend: ()=> {
                $("#stat-btn").attr('disabled', true);
                $("#stat-response").html("<span class='spinner-border spinner-border-sm text-info'></span> processing...")
            },
            success: (data) => {
                setTimeout(() => {
                    $("#stat-response").html(data);
                }, 1000);
            },
            complete: ()=> {
                $("#stat-btn").attr('disabled', false);
            }
        });
    });  
}

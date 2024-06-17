<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Directors</title>
    <link rel="stylesheet" type="text/css" href="style/countries.css" media="screen" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="shortcut icon" href="images/main.png">
    <script src="scripts/d3.js"></script>
    <script src="scripts/jquery.js"></script>
    <script src="scripts/topojson.js"></script>
</head>

<style>

</style>
<div class="tooltip"></div>

<body>
    <header>
        <h1 id="title" style="text-align: center; width: 100%; font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;">
            Movie Director Ratings
        </h1>
    </header>
    <br />
    <div class="row">
        <div class="column" style="width:100%">
            <h1 id="country_name">

            </h1>
            <button type="button" class="btn btn-secondary" id="btnReset" onclick="reset()">Reset</button>
            <div class="dropdown">
                <button class="btn btn-secondary" style="margin-bottom: 10px;">Movie Years</button>
                <div class="dropdown-content" id="mainContainer">
                    <button class="dropdown-item" type="button" onClick="changeYear(2005)">2005</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2006)">2006</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2007)">2007</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2008)">2008</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2009)">2009</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2010)">2010</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2011)">2011</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2012)">2012</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2013)">2013</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2014)">2014</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2015)">2015</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2016)">2016</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2017)">2017</button>
                    <button class="dropdown-item" type="button" onClick="changeYear(2018)">2018</button>
                </div>
            </div>
            <button style="margin-bottom: 10px;" class="btn btn-success" onClick="goToComparision()" id="comparedirectors">Compare Directors</button>

            <div id="map">

            </div>
            <button style="margin-top: 10px;" type="button" class="btn btn-success" onclick="startTimer()" id="btnStart">Start Animation</button>
            <button style="margin-top: 10px;" type="button" class="btn btn-danger" onclick="stopTimer()" id="btnStop" disabled>Stop</button>
        </div>
        <div class="column" style="width:100%">
            <div class="column" style="width:max-width">
                <table style="margin-top:80px">
                    <thead>
                        <th>
                            <p style="font-size:22px">Movies per country in year</p>
                        </th>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="3" style="margin:0">
                                <div id="colorGradient" class="list-group-item d-flex justify-content-between">
                            </td>
                            <td>6+</td>
                        </tr>
                        <tr>
                            <td>3-6</td>
                        </tr>
                        <tr>
                            <td>0-3</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="column movieinfo" style="width:max-width; margin-top:80px">
                <h1 id="information">
                    Movie industry info
                </h1>
                <div class="moviesinfo" id="country" style="font-size:20px;margin-top:40px" id="country">Country: </div>
                <div class="moviesinfo" id="movies" style="font-size:20px;margin-top:20px" id="movies">Num of movies: </div>
                <div class="moviesinfo" id="directors" style="font-size:20px;margin-top:20px" id="directors">Num of directors: </div>
                <div class="moviesinfo" id="imdbscore" style="font-size:20px;margin-top:20px" id="imdbscore">Best Imdb: </div>
                <div class="moviesinfo" id="metascore" style="font-size:20px;margin-top:20px" id="metascore">Best Metascore: </div>
                <div class="moviesinfo" id="rtscore" style="font-size:20px;margin-top:20px" id="rtscore">Best RTscore: </div>
            </div>
        </div>
    </div>
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="https://d3js.org/topojson.v2.min.js"></script>
    <script>
        //Get default year 
        var countries = [];
        var filteredMovies = [];
        var year = window.localStorage.getItem("year");
        if (year == null) {
            window.localStorage.setItem("year", 2000);
            year = 2000;
        }
        var startDate = 2005;
        var lastDate = year;

        //Find elements
        var tooltip = d3.select("div.tooltip");
        var btnStart = document.getElementById("btnStart"),
            add;

        var btnStop = document.getElementById("btnStop");
        var name = document.getElementById("information");
        var countryName = document.getElementById("country");
        var numOfMovies = document.getElementById("movies");
        var numOfDirectors = document.getElementById("directors");
        var bestImdb = document.getElementById("imdbscore");
        var bestMetascore = document.getElementById("metascore");
        var bestRTscore = document.getElementById("rtscore");

        //Color scale
        var colors = d3.scaleQuantize()
            .domain([0, 6])
            .range(["#FFDFAE", "#FDB950", "#FEA720"]);

        var width = window.innerWidth / 2,
            height = window.innerHeight / 2,
            active = d3.select(null);

        var projection = d3.geoMercator()
            .scale(150)
            .translate([width / 2, height / 2]);

        //Add zoom to countries
        var zoom = d3.zoom().on("zoom", zoomed);
        var path = d3.geoPath().projection(projection);

        //Create SVG element
        var svg = d3.select("#map").append("svg")
            .attr("width", width + 30)
            .attr("height", height)
            .on("click", stopped, true);

        svg.append("rect")
            .attr("class", "background")
            .attr("width", width)
            .attr("height", height)
            .on("click", reset);
        var g = svg.append("g");
        svg.call(zoom);

        //Filter movies for selected year
        filterByYear(year);

        //Add title
        var countryNameHeader = document.getElementById("country_name");
        countryNameHeader.innerText = "Year - " + year;

        function clicked(d) {
            if (active.node() === this) {
                return reset();
            }
            active.classed("active", false);
            active = d3.select(this).classed("active", true);

            //Set information about selected country
            var name = countries.find(x => x.id == d.id).name;
            countryNameHeader.innerText = "Country - " + name + "(" + year + ")";
            name.innerText = "Movie industry info in " + name;
            countryName.innerText = "Country: " + name;

            window.localStorage.setItem("country", name);

            var selectedCountryMovies = filteredMovies.filter(function(item) {
                return item.Country.includes(name);
            })

            if (selectedCountryMovies.length > 0) {
                var imdbScore = Math.max.apply(null, selectedCountryMovies.map(function(item) {
                    return parseInt(item.IMdb_score)
                }));
                var metaScore = Math.max.apply(null, selectedCountryMovies.map(function(item) {
                    return parseInt(item.Metascore)
                }));
                var rtScore = Math.max.apply(null, selectedCountryMovies.map(function(item) {
                    return parseInt(item.RT_score)
                }));
                var countDirectors = [...new Set(selectedCountryMovies.map(item => item.director_1))].length;
            } else {
                var countDirectors = 0;
                var imdbScore = "No score";
                var metaScore = "No score";
                var rtScore = "No score";
            }
            numOfMovies.innerText = "Num of movies: " + selectedCountryMovies.length;
            numOfDirectors.innerText = "Num of directors: " + countDirectors;
            bestImdb.innerText = "Best Imdb: " + imdbScore;
            bestMetascore.innerText = "Best Metascore: " + metaScore;
            bestRTscore.innerText = "Best RTscore: " + rtScore;

            //Zoom selected country
            var bounds = path.bounds(d),
                dx = bounds[1][0] - bounds[0][0],
                dy = bounds[1][1] - bounds[0][1],
                x = (bounds[0][0] + bounds[1][0]) / 2,
                y = (bounds[0][1] + bounds[1][1]) / 2,
                scale = Math.max(1, Math.min(8, 0.9 / Math.max(dx / width, dy / height))),
                translate = [width / 2 - scale * x, height / 2 - scale * y];

            svg.transition()
                .duration(750)
                // .call(zoom.translate(translate).scale(scale).event); // not in d3 v4
                .call(zoom.transform, d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale)); // updated for d3 v4
        }

        function getColorNumber(id) {
            var country = countries.filter(x => x.id == id) //Filter by ID of country to find selected country
            var countMovies = filteredMovies.filter(x =>
                x.Country.includes(country[0].name)
            ) //Filter to find only movies for selected country
            return countMovies.length;
        }

        function filterByYear(newYear) {
            //Filter movies from json for selected Year
            d3.json("movies.json", function(error, dataset) {
                filteredMovies = dataset.filter(x => x.Year == newYear);

                d3.selectAll("path").style("fill", "#FFDFAE");
                loadMap(newYear)
                reset()
            });
        }

        function reset() {
            countryNameHeader.innerText = "Year - " + year;
            active.classed("active", false);
            active = d3.select(null);
            resetInfo()
            svg.transition()
                .duration(750)
                .call(zoom.transform, d3.zoomIdentity);
        }

        function resetInfo() {
            country.innerText = "Country:";
            numOfMovies.innerText = "Num of movies: ";
            numOfDirectors.innerText = "Num of directors: ";
            bestImdb.innerText = "Best Imdb: ";
            bestMetascore.innerText = "Best Metascore: ";
            bestRTscore.innerText = "Best RTscore: ";
        }

        function loadMap(year) {
            //Load Topojson Map
            d3.json("country_names.json", function(error, data) {
                countries = data;

                d3.json("world-50m.json", function(error, world) {
                    if (error) throw error;
                    g.selectAll("path").remove()
                    g.selectAll("path")
                        .data(topojson.feature(world, world.objects.countries).features)
                        .enter().append("path")
                        .attr("d", path)
                        .attr("class", "feature")
                        .style("fill", function(d) {
                            return colors(getColorNumber(d.id));
                        })
                        .on("click", clicked)
                        .on("mouseover", function(d, i) {
                            return tooltip.style("hidden", false).html(countries.find(x => x.id == d.id).name);
                        })
                        .on("mousemove", function(d) {
                            tooltip.classed("hidden", false)
                                .style("top", (d3.event.pageY) + "px")
                                .style("left", (d3.event.pageX + 10) + "px")
                                .html(countries.find(x => x.id == d.id).name);
                        })
                        .on("mouseout", function(d, i) {
                            tooltip.classed("hidden", true);
                        });

                    g.append("path")
                        .datum(topojson.mesh(world, world.objects.countries, function(a, b) {
                            return a !== b;
                        }))
                        .attr("class", "mesh")
                        .attr("d", path);

                });
            });
        }

        //Timer for animation
        function startTimer() {
            btnStart.disabled = true;
            btnStop.disabled = false;
            add = setInterval(function() {
                changeYear(startDate)
                startDate++;
                if (startDate == 2016) {
                    stopTimer();
                }
            }, 1500);
        }

        function stopTimer() {
            clearInterval(add);
            startDate = 2005;
            changeYear(lastDate)
            btnStart.disabled = false;
            btnStop.disabled = true;
        }

        function changeYear(newYear) {
            window.localStorage.setItem("year", newYear);
            year = newYear;
            filterByYear(newYear)
        }

        //Go to Index page
        function goToComparision() {
            window.location = "index.php"
        }

        function zoomed() {
            g.style("stroke-width", 1.5 / d3.event.transform.k + "px");
            g.attr("transform", d3.event.transform); // updated for d3 v4
        }

        // If the drag behavior prevents the default click,
        // also stop propagation so we don’t click-to-zoom.
        function stopped() {
            if (d3.event.defaultPrevented) d3.event.stopPropagation();
        }
    </script>
</body>

</html>
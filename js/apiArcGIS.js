    require(["esri/map",
		"dojo/parser",
		"dijit/layout/BorderContainer",
        "dijit/layout/ContentPane",
		
	   "js/geojsonlayer.js",
        
        "dojo/on",
        "dojo/query",
        "dojo/dom",		
        "dojo/domReady!"
		],
      function (Map, BorderContainer, ContentPane, parser,
		GeoJsonLayer, on, query, dom) {

	 
        // Create map
        var map = new Map("mapArcGIS", {
			basemap: "streets-navigation-vector", //streets-relief-vector, streets-navigation-vector, osm (OpenStreetMap)
            center: [-3.703, 40.417],
            zoom: 11,
			maxZoom:16,
			logo:false // dont display de esri logo			
        });
		
		//map.infoWindow.domNode.className += " dark";
        
		
        map.on("load", function () {
          addGeoJsonLayer("js/AATTbW_GeoJson.json");			
        });
	
		
	
        function addGeoJsonLayer(url) {
            // Create the layer
            var geoJsonLayer = new GeoJsonLayer({
                url: url
            });

           
            // Zoom to layer
            geoJsonLayer.on("update-end", function (e) {
                map.setExtent(e.target.extent.expand(1.3));
			
            });
			
	
            // Add to map
            map.addLayer(geoJsonLayer);

				
		}

    });

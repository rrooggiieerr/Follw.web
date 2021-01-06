'use strict';

class Follw {
	map = null;
	marker = null;
	accuracy = null;
	textOverlay = null;

	onLocationChangeHooks = [];
	onIDDeletedHooks = [];

	updateMultiplier = 1;
	stopUpdate = false;
	updateTimeoutID = null;
	
	offScreen = false;
	hidden;
	
	constructor(element, followURL, zoom = 12) {
		this.element = element;
		this.followURL = followURL + ".json";
		this.zoom = zoom

		// See if DOM is already available
		if (document.readyState === 'complete' || document.readyState === 'interactive') {
			// call on next available tick
			var _this = this;
			setTimeout(function() {
				_this.init();
			}, 1);
		} else {
			var _this = this;
			document.addEventListener('DOMContentLoaded', function() {
				_this.init();
			});
		}
	}

	init() {
		this.element = document.getElementById(this.element);
		this.map = L.map(this.element, { zoomSnap: 0 }).fitWorld();
		L.control.scale().addTo(this.map);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
			maxZoom: 19
		}).addTo(this.map);
		
		var _this = this;
		
		// Decrease update interval if window is not in focus
		var _onblur = window.onblur;
		window.onblur = function() {
			if(_onblur != null)
				_onblur();
			console.debug("blur");
			_this.updateMultiplier = 2;
		}

		// Set update interval back to normal if window is in focus
		var _onfocus = window.onfocus;
		window.onfocus = function() {
			if(_onfocus != null)
				_onfocus();
			console.debug("focus");
			_this.updateMultiplier = 1;
		}

		// Set the name of the "hidden" property and the change event for visibility
		var visibilityChangeEvent; 
		if (typeof document.hidden !== 'undefined') {
			this.hidden = 'hidden';
			visibilityChangeEvent = 'visibilitychange';
		} else if (typeof document.mozHidden !== 'undefined') { // Firefox up to v17
			this.hidden = 'mozHidden';
			visibilityChangeEvent = "mozvisibilitychange";
		} else if (typeof document.webkitHidden !== 'undefined') { // Chrome up to v32, Android up to v4.4, Blackberry up to v10
			this.hidden = 'webkitHidden';
			visibilityChangeEvent = 'webkitvisibilitychange';
		}

		if(typeof document.addEventListener === 'undefined' || typeof document[this.hidden] === 'undefined') {
			// If the browser doesn't support addEventListener or the Page Visibility API just act as if object is visible
			console.error("Browser doesn't support addEventListener or the Page Visibility API");
			this.hidden = 'hidden';
			document[this.hidden] = false;
			this.onVisible();
		} else {
			// Handle page visibility change
			//var _this = this;
			document.addEventListener(visibilityChangeEvent, function() {
				// If the page is hidden, pause updating the location;
				// if the page is shown, continue updating the location
				if(_this.offScreen) {
					// Do nothing, visibility is handeled by the Intersection Observer API
					console.debug("Visibility is handeled by the Intersection Observer API");
				} else if(document[_this.hidden]) {
					_this.onInvisible();
				} else {
					_this.onVisible();
				}
			}, false);

			if(!document[this.hidden]) {
				this.onVisible();
			}
		}
		
		// Detect if map is off screen
		this.observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if(document[_this.hidden]) {
					// Do nothing, visibility is handeled by the Page Visibility API
					console.debug("Visibility is handeled by the Page Visibility API");
				} else if(entry.intersectionRatio == 0) {
					_this.offScreen = true;
					_this.onInvisible();
				} else {
					_this.offScreen = false;
					_this.onVisible();
				}
			});
		}, { root: document.documentElement });
		this.observer.observe(this.element);
	}

	onVisible() {
		console.debug("visible");
		//this.invalidateSize();
		this.stopUpdate = false;
		if(this.updateTimeoutID === null) {
			this.updateTimeoutID = -1;
			this.getLocation();
		}
	}
	
	onInvisible() {
		console.debug("invisible");
		this.stopUpdate = true;
		if(this.updateTimeoutID !== null) {
			clearTimeout(this.updateTimeoutID);
			this.updateTimeoutID = null;
		}
	}

	setTextOverlay(text) {
		if(text) {
			if(this.textOverlay == null) {
				this.textOverlay = document.createElement('div');
				this.textOverlay.id = this.element.id + '_textoverlay';
				this.textOverlay.style.zIndex = 500;
				this.textOverlay.style.position = 'relative';
				this.textOverlay.style.textAlign = 'center';
				this.textOverlay.style.top = '50%';
				this.textOverlay.style.transform = 'translate(0%, -50%)';
				this.element.appendChild(this.textOverlay);
			}
			this.textOverlay.innerHTML = text;
		} else if(this.textOverlay) {
			this.textOverlay.innerHTML = "";
		}
	}

	setMarker(location, accuracy) {
		if(location === null) {
			//_this.map.setView([0, 0], 2);
			this.map.fitWorld();
			this.map.zoomControl.disable();
			this.map.dragging.disable();
			this.map.touchZoom.disable();
			this.map.doubleClickZoom.disable();
			this.map.scrollWheelZoom.disable();
	
			if(this.marker != null) {
				this.map.removeLayer(this.marker);
				this.marker = null;
			}
			
			if(this.accuracy != null) {
				this.map.removeLayer(this.accuracy);
				this.accuracy = null;
			}
			
			this.setTextOverlay("No location is currently being shared");
		} else {
			this.map.zoomControl.enable();;
			this.map.dragging.enable();
			this.map.touchZoom.enable();
			this.map.doubleClickZoom.enable();
			this.map.scrollWheelZoom.enable();
	
			if(this.marker == null) {
				this.map.setView(location, this.zoom);
				this.marker = L.circleMarker(location, {radius: 4, stroke: false, fillOpacity: 1}).addTo(this.map);
			} else {
				this.map.setView(location);
				this.marker.setLatLng(location);
			}
	
			if(accuracy != null) {
				if(this.accuracy == null) {
					this.accuracy = L.circle(location, {radius: accuracy, weight: 1}).addTo(this.map);
				} else {
					this.accuracy.setLatLng(location);
					this.accuracy.setRadius(accuracy);
				}
			} else if(this.accuracy != null) {
				// Remove the accuracy circle
				this.map.removeLayer(this.accuracy);
				this.accuracy = null;
			}

			this.setTextOverlay(null);
		}
	}

	getLocation(once = false) {
		var request = new XMLHttpRequest();
		request.open('GET', this.followURL, true);
		var _this = this;
		request.onload = function() {
			if (this.status == 200) {
				var data = JSON.parse(this.response);

				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp == null || _this.lastTimestamp != data.timestamp) {
					_this.lastTimestamp = data.timestamp;

_this.setMarker([data.latitude, data.longitude], data.accuracy);

					_this.onLocationChangeHooks.forEach(function(hook) {
						hook(data);
					});
				}

				if(!once && !_this.stopUpdate) {
					var update = 1;
					if(typeof data.refresh != "undefined")
						update = data.refresh;
					_this.updateTimeoutID = setTimeout(function() {
						_this.getLocation();
					}, update * _this.updateMultiplier * 1000);
				}
			} else if (this.status == 410) {
				console.info("ID has been deleted");
				// Deleted
				_this.onIDDeletedHooks.forEach(function(hook) {
					hook();
				});
				_this.setTextOverlay("Follw ID is deleted");
			} else if (this.status == 404) {
				console.error("ID does not exist");
			} else {
				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp != null) {
					_this.lastTimestamp = null;
	
					_this.setMarker(null, null);
					
					_this.onLocationChangeHooks.forEach(function(hook) {
						hook(null);
					});
				}

				_this.updateTimeoutID = setTimeout(function() {
					_this.getLocation(once);
				}, _this.updateMultiplier * 1000);
			}
		};

		request.send();
	}
	
	onLocationChange(hook) {
		this.onLocationChangeHooks.push(hook);
	}
	
	onIDDeleted(hook) {
		this.onIDDeletedHooks.push(hook);
	}
	
	invalidateSize() {
		this.map.invalidateSize();
	}
	
	prettyPrintCoordinates(latitude, longitude) {
		var toDMS = function(coordinate, cardinals) {
			var absolute = Math.abs(coordinate);
			var degrees = Math.floor(absolute);
			var minutesNotTruncated = (absolute - degrees) * 60;
			var minutes = Math.floor(minutesNotTruncated);
			var seconds = Math.floor((minutesNotTruncated - minutes) * 60);
			var cardinal = coordinate >= 0 ? cardinals.charAt(0) : cardinals.charAt(1);
		
			return degrees + "° " + minutes + "′ " + seconds + "″ " + cardinal;
		}
		
		return toDMS(latitude, "NS") + " " + toDMS(longitude, "EW");
	}
}
'use strict';

class Follw {
	constructor(element, followURL, zoom = 14) {
		this.element = element;
		this.zoom = zoom

		this.marker = null;
		this.accuracy = null;
		this.textOverlay = null;

		this.callbacks = [];

		this.updateMultiplier = 1;
		this.pause = false;
		this.stopUpdate = false;
		this.updateTimeoutID = null;

		this.offScreen = false;
		this.offline = false;
		this.timeoutCounter = 0;
		
		this.translations = {'nolocation': 'No location is currently being shared',
			'offline': 'Offline',
			'iddeleted': 'Follw ID is deleted'
		};

		if(followURL.endsWith('.json'))
			this.followURL = followURL;
		else
			this.followURL = followURL + ".json";
			
		// See if DOM is already available
		if (document.readyState === 'complete' || document.readyState === 'interactive') {
			// call on next available tick
			console.debug('DOM available, wait 1 tick');
			var _this = this;
			setTimeout(function() {
				_this.init();
			}, 1);
		} else {
			console.debug('Wait for DOM to become available');
			var _this = this;
			document.addEventListener('DOMContentLoaded', function() {
				console.debug('DOMContentLoaded');
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

		this.addEventListener('locationchanged', (_this, data) => {
			if(data)
				_this.setMarker([data.latitude, data.longitude], data.accuracy);
			else
				_this.setMarker(null, null);
		});

		this.addEventListener('iddeleted', function(_this) {
			_this.setTextOverlay(_this.translations['iddeleted']);
		});

		this.addEventListener('online', function(_this) {
			_this.setTextOverlay(null);
		});

		this.addEventListener('offline', function(_this) {
			_this.setTextOverlay(_this.translations['offline']);
		});
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

			this.setTextOverlay(this.translations['nolocation']);
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
		request.timeout = 1000;

		var _this = this;
		request.onload = function() {
			if (this.status == 200) {
				var data = JSON.parse(this.response);

				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp == null || _this.lastTimestamp != data.timestamp || _this.offline) {
					_this.setTextOverlay(null);
					_this.offline = false;
					_this.lastTimestamp = data.timestamp;
					_this.trigerEvent('locationchanged', data);
				}

				if(!once && !_this.stopUpdate) {
					var update = 1;
					if(typeof data.refresh != "undefined")
						update = data.refresh;
					_this.updateTimeoutID = setTimeout(function() {
						_this.getLocation();
					}, update * _this.updateMultiplier * 1000);
				}
				return;
			} else if (this.status == 404) {
				// Non existent
				console.error("ID does not exist");
				return;
			} else if (this.status == 410) {
				// Deleted
				console.info("ID has been deleted");
				_this.trigerEvent('iddeleted');
				return;
			} else if (this.status == 503) {
				// Maintenance
				_this.trigerEvent('offline');
			} else {
				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp != null || _this.offline) {
					_this.offline = false;
					_this.lastTimestamp = null;
					_this.trigerEvent('locationchanged', null);
				}
			}

			_this.updateTimeoutID = setTimeout(function() {
				_this.getLocation(once);
			}, _this.updateMultiplier * 1000);
		};

		request.ontimeout = function() {
			console.error('Timeout');
			this.timeoutCounter++;
			
			if(this.timeoutCounter > 3) {
				_this.offline = true;
				_this.trigerEvent('offline');
			}

			_this.updateTimeoutID = setTimeout(function() {
				_this.getLocation(once);
			}, _this.updateMultiplier * 1000);
		};

		request.onerror = function() {
			console.error('Error');
			_this.offline = true;
			_this.trigerEvent('offline');

			_this.updateTimeoutID = setTimeout(function() {
				_this.getLocation(once);
			}, _this.updateMultiplier * 1000);
		};

		request.send();
	}

	addEventListener(type, listener) {
		if(['locationchanged', 'iddeleted', 'offline', 'online'].includes(type)) {
			if(!(type in this.callbacks))
				this.callbacks[type] = [];

			this.callbacks[type].push(listener);

			return true;
		}
		
		return false;
	}

	trigerEvent(type, data) {
		if(type in this.callbacks) {
			this.callbacks[type].forEach(hook => hook(this, data));
			return true;
		}

		return false;
	}

	invalidateSize() {
		if(this.map !== null) {
			this.map.invalidateSize();
		}
	}

	startUpdate() {
		if(this.updateTimeoutID !== null)
			return

		var _this = this;

		if (document.readyState === "complete" || document.readyState === "loaded") {
			console.debug('Starting location update');

			var onVisible = function() {
				console.debug("visible");
				//this.invalidateSize();
				_this.stopUpdate = false;
				if(!_this.pause && _this.updateTimeoutID === null) {
					_this.updateTimeoutID = -1;
					_this.getLocation();
				}
			}

			var onInvisible = function() {
				console.debug("invisible");
				_this.stopUpdate = true;
				if(_this.updateTimeoutID !== null) {
					clearTimeout(_this.updateTimeoutID);
					_this.updateTimeoutID = null;
				}
			}

			// Set the name of the "hidden" property and the change event for visibility
			var hidden;
			var visibilityChangeEvent; 
			if (typeof document.hidden !== 'undefined') {
				hidden = 'hidden';
				visibilityChangeEvent = 'visibilitychange';
			} else if (typeof document.mozHidden !== 'undefined') { // Firefox up to v17
				hidden = 'mozHidden';
				visibilityChangeEvent = "mozvisibilitychange";
			} else if (typeof document.webkitHidden !== 'undefined') { // Chrome up to v32, Android up to v4.4, Blackberry up to v10
				hidden = 'webkitHidden';
				visibilityChangeEvent = 'webkitvisibilitychange';
			}

			if(typeof document.addEventListener === 'undefined' || typeof document[hidden] === 'undefined') {
				// If the browser doesn't support addEventListener or the Page Visibility API just act as if object is visible
				console.error("Browser doesn't support addEventListener or the Page Visibility API");
				hidden = 'hidden';
				document[hidden] = false;
				onVisible();
			} else {
				// Handle page visibility change
				//var _this = this;
				document.addEventListener(visibilityChangeEvent, function() {
					// If the page is hidden, pause updating the location;
					// if the page is shown, continue updating the location
					if(_this.offScreen) {
						// Do nothing, visibility is handeled by the Intersection Observer API
						console.debug("Visibility is handeled by the Intersection Observer API");
					} else if(document[hidden]) {
						onInvisible();
					} else {
						onVisible();
					}
				}, false);

				if(!document[hidden]) {
					onVisible();
				}
			}

			// Detect if map is off screen
			var observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if(document[hidden]) {
						// Do nothing, visibility is handeled by the Page Visibility API
						console.debug("Visibility is handeled by the Page Visibility API");
					} else if(entry.intersectionRatio == 0) {
						_this.offScreen = true;
						onInvisible();
					} else {
						_this.offScreen = false;
						onVisible();
					}
				});
			}, { root: document.documentElement });
			observer.observe(_this.element);
		
			window.addEventListener('online', function() {
				console.debug('Browser might be online');
				_this.resumeUpdate();
			});

			window.addEventListener('offline', function() {
				console.debug('Browser is offline');
				_this.pauseUpdate();
				_this.offline = true;
				_this.trigerEvent('offline');
			});
		} else if(document.readyState === "interactive") {
			setTimeout(function() {
				_this.startUpdate();
			}, 100);
		} else {
			document.addEventListener('DOMContentLoaded', function() {
				_this.startUpdate();
			});
		};
	}

	pauseUpdate() {
		if(!this.pause) {
			console.debug('Pausing location update');
			this.pause = true;
			if(this.updateTimeoutID !== null) {
				clearTimeout(this.updateTimeoutID);
				this.updateTimeoutID = null;
			}
		}
	}

	resumeUpdate() {
		if(this.pause) {
			console.debug('Resuming location update');
			this.pause = false;
			if(this.updateTimeoutID === null) {
				this.updateTimeoutID = -1;
				this.getLocation();
			}
		}
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

	prettyPrintTime(timestamp) {
		return new Date(timestamp).toLocaleTimeString();
	}
}
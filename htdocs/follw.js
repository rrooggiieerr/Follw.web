class Follw {
	map = null;
	marker = null;
	accuracy = null;
	textOverlay = null;
	onLocationChangeHooks = [];
	onIDDeletedHooks = [];
	stopUpdate = false;
	timeoutID = null;
	
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

		var _onblur = window.onblur;
		window.onblur = function() {
			if(_onblur != null)
				_onblur();
			console.log("blur");
			//_this.onInvisible();
		}

		var _onfocus = window.onfocus;
		window.onfocus = function() {
			if(_onfocus != null)
				_onfocus();
			console.log("focus");
			//if(_this.element.offsetParent !== null)
			//	_this.onVisible();
		}

		this.observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if(entry.intersectionRatio == 0) {
					_this.onInvisible();
				} else {
					_this.onVisible();
				}
			});
		}, { root: document.documentElement });
		this.observer.observe(this.element);
	}

	onVisible() {
		console.log("visible");
		//this.invalidateSize();
		this.stopUpdate = false;
		if(this.timeoutID === null) {
			/*this.timeoutID = setInterval(function() {
				console.log("visible");
			}, 500);*/
			this.getLocation();
		}
	}
	
	onInvisible() {
		console.log("invisible");
		this.stopUpdate = true;
		if(this.timeoutID !== null) {
			clearTimeout(this.timeoutID);
			this.timeoutID = null;
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
					var interval = 1;
					if(typeof data.interval != "undefined")
						interval = data.interval;
					_this.timeoutID = setTimeout(function() {
						_this.getLocation();
					}, interval * 1000);
				}
			} else if (this.status == 410) {
				// Deleted
				_this.onIDDeletedHooks.forEach(function(hook) {
					hook();
				});
				_this.setTextOverlay("Follw ID is deleted");
			} else {
				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp != null) {
					_this.lastTimestamp = null;
	
					_this.setMarker(null, null);
					
					_this.onLocationChangeHooks.forEach(function(hook) {
						hook(null);
					});
				}

				_this.timeoutID = setTimeout(function() {
					_this.getLocation(once);
				}, 1000);
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
}
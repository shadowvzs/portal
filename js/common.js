function checkFormData(form) {
	const VALIDATOR = {
		'NUMBER': /^[0-9]+$/,
		'ALPHA': /^[a-zA-Z]+$/,
		'ALPHA_NUM': /^[a-zA-Z0-9]+$/,
		'ALPHA_NUM_': /^[a-zA-Z_0-9]+$/,
		'STR_AND_NUM': /^([0-9]+[a-zA-Z]+|[a-zA-Z]+[0-9]+|[a-zA-Z]+[0-9]+[a-zA-Z]+)$/,
		'LOW_UP_NUM': /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/,
		'SLUG': /^[a-zA-Z0-9-_]+$/,
		'NAME': '',
		'HOST': /^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/,
		'NAME_HUN': /^([a-zA-Z0-9 ÁÉÍÓÖŐÚÜŰÔÕÛáéíóöőúüűôõû]+)$/,
		'STRING': null,
		//'STRING': /^((?!script).)*$/,
		'EMAIL': /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/,
		'IP': /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
	};			
	// select inputs in "form" element
	// store input value with key but we remove the prefix
	// form is login, then input id and name mut have "login_" prefix
	// ex. in login form <input name="login_password"> but we store "password"
	let inputs = form.querySelectorAll('input, select, textarea'), value, rule, val_len,
	name, prefix = form.id.split('_')[0], len = prefix.length+1, param = {};
	for (let input of inputs) {
		name = input.id;
		if  (!name && name.length > len) { continue; }
		name = name.substr(len);
		value = input.value;
		if (input.dataset.rule) {
			val_len = value.length;
			rule = input.dataset.rule.split(',');
			// temporary for test i skip regex
			if (!VALIDATOR[rule[0]]) { param[name] = value; continue; }
			if ((rule[1] > 0) && (!VALIDATOR[rule[0]].test(value) || val_len < rule[1] || val_len > rule[2])) {
				alert(input.title ? input.title : `Invalid data at ${name} field (${rule[1]}, ${rule[2]})`);
				input.focus();
				return false;
			}
		}
		if (input.dataset.same) {
			rule = document.getElementById(input.dataset.same);
			if (!rule || value !== rule.value) {
				alert(input.title ? input.title : name+' not same than '+rule.name+' field');
				input.focus();
				return false;
			}
		}
		param[name] = value;
	}
	return form.submit();
}		

let UserComponent;

document.addEventListener("DOMContentLoaded", function(event) {
	//yeah, i know i can use jsTimer aswell, but i think this way safer
	const e = document.getElementById('jsTimer');
	if (e) {
		let t;
		setInterval( ()=>{ 
			t = new Date();
			e.textContent = ('0'+t.getHours()).slice(-2)+':'+('0'+t.getMinutes()).slice(-2)+':'+('0'+t.getSeconds()).slice(-2);
		}, 1000);
	}
	
	function Ajax (setup, success, error) {

		if (typeof error != "function" || typeof success != "function") { return alert('Missing classback(s)....'); }
		if (!setup || !setup.url) { return error('no settings for request'); }
		var type = (!/(GET|POST|PUT|DELETE)/.test(setup.method)) ? "GET": setup.method ; 
		var url = setup.url;
		var data = setup.data;
		var httpRequest = new XMLHttpRequest();     
		
		if ((!data || (Object.keys(data).length === 0 && data.constructor === Object))) {
			data = null;
		} else if (type === "GET") {
			url += (~url.indexOf("?") ? "&" : "?") + serialize(data);
			data = null;
		}

		
		httpRequest.onreadystatechange = function(event) {
		
			if (this.readyState === 4) {
				if (this.status === 200) {
					if (!this.response) { error("no returned data"); return false; }
					if (!this.response.success) { return error(this.response); }
					success (this.response.data || this.response);
				} else {
					error(this.status);
				}
			}
		};
		
		httpRequest.responseType = 'json';
		httpRequest.open(type, encodeURI(url), true);

		if (type !== "POST" || !data) {
			httpRequest.send();
		}else{
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			httpRequest.send(serialize(setup.data));
		}
	}

	var serialize = function(obj, prefix) {
		var str = [], p;
			for(p in obj) {
				if (obj.hasOwnProperty(p)) {
					var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
					str.push((v !== null && typeof v === "object") ?
						serialize(v, k) :
						encodeURIComponent(k) + "=" + encodeURIComponent(v));
				}
			}
		return str.join("&");
	};

	function UserPanel() {
		const tableDiv = document.querySelector('.userTable'),
			parent = tableDiv.parentElement,
			url = {
				get() { return `index.php?controller=user&action=list&ajax=true`; },
				status(id, n) { return `index.php?controller=user&action=change_status&param[status]=${n}&param[id]=${id}&ajax=true`; },
				rank(id) { return `index.php?controller=user&action=change_rank&param[id]=${id}&ajax=true`; },
			}
			table = tableDiv.querySelector('table'),
			userEdit = parent.querySelector('.userEdit'),
			userEditName = userEdit.querySelector('h2'),
			userStatus = userEdit.querySelector('select'),
			changeRankButton = userEdit.querySelector('#toggle_rank'),
			rank = ['Guest', 'User', 'Admin', 'Owner'],
			statuses = ['Inactive', 'Active', 'Banned', 'Deleted'];
		let list = [];
		
		// ajax callbacks
		const render = {
			table(data) {
				let html = '';
				list = data;
				data.forEach( e => {
					html += createRow(e);
				});
				table.innerHTML = html;
				table.addEventListener('click', handler.select);			
			},
			rank(data) {
				list.forEach((u, i) => {
					if (u.id == data.id) {
						table.querySelector('tr[data-id="'+data.id+'"] td.rank').textContent = rank[data.rank] || 'Unknown';
						list[i].rank = data.rank; 
						return;
					}
				});
			},
			status(data) {
				list.forEach((u, i) => {
					if (u.id == data.id) {
						if (data.status == 4) {
							table.querySelector('tr[data-id="'+data.id+'"]').remove();
							list.splice(i, 1);
						} else {
							table.querySelector('tr[data-id="'+data.id+'"] td.status').textContent = statuses[data.status] || 'Unknown';
							list[i].status = data.status; 
						}
						return;
					}
				});				
			}, 
			error(data={}) {
				console.log(data);
				alert(data.message || 'Operation failed');
			}
		}
		
		// for events
		const handler = {
			status(ev) {
				const id = userEdit.dataset.id,
					status = userStatus[userStatus.selectedIndex].value;
				Ajax({url: url.status(id, status)},render.status, render.error);				
			},
			rank(ev){
				const id = userEdit.dataset.id;
				Ajax({url: url.rank(id)},render.rank, render.error);
			},
			select(ev) {
				let e = ev.target, t = null;
				do {
					if (e.tagName == "TABLE") {
						break;
					} else if (e.tagName != "TR") {
						e = e.parentElement;
					} else {
						t = e;
					}
				} while (e.tagName != 'TABLE' && !t);
				
				if (t) {
					const id = t.dataset.id;
					userEdit.style.display = "inline-block";
					for (let user of list) {
						if (user.id == id) {
							userEditName.textContent = user.name;
							userEdit.dataset.id = user.id;
							userStatus.selectedIndex = user.status;
							break;
						}
					}
				}				
			}
		}

		function createRow(e) {
			return `<tr data-id="${e.id}">
					<td class='name' title="Registered: ${e.created}\x0ALast login: ${e.updated}\x0ARank: ${rank[e.rank]}">${e.name}</td>
					<td class='status'>${statuses[e.status]}</td>
					<td class="rank xxs-hidden">${rank[e.rank]}</td>
					<td class="created xs-hidden ms-hidden">${e.created.split('-').join('.')}</td>
					<td class="updated ms-hidden xs-hidden">${e.updated.split('-').join('.')}</td>
				</tr>`;
		}
		
		function init() {
			Ajax({url: url.get()},render.table, render.error);
			userStatus.addEventListener('change', handler.status);
			changeRankButton.addEventListener('click', handler.rank);

		}
		
		init();
		
		return {
			toggle(){
				parent.classList.toggle('show');
			},
			remove(){
				table.removeEventListener('click', handler.select);
				userStatus.removeEventListener('change', handler.status);
				changeRankButton.removeEventListener('click', handler.rank);	
				parent.remove();				
			}
		};
	}
	
	UserComponent = new UserPanel();
	
});
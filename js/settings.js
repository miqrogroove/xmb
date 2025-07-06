function switchTab(myButton, bodyID) {
  let i = 0;

  // Get all table blocks and hide them
  let table = document.getElementById('settings');
  let bodies = table.getElementsByTagName('tbody');
  for (i = 0; i < bodies.length; i++) {
    bodies[i].style.display = "none";
  }

  // Get all buttons and remove the class "active"
  let mydiv = document.getElementById('tabs');
  let buttons = mydiv.getElementsByTagName('button');
  for (i = 0; i < buttons.length; i++) {
    buttons[i].className = '';
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(bodyID).style.display = "table-row-group";
  myButton.className = "active";
}

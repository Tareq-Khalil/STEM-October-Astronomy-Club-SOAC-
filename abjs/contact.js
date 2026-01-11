(async () => {
  let contacts = await fetch("/contactinfo.json");
  contacts = await contacts.json();
  const emails = document.querySelectorAll(".email");
  emails.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      let name = e.target.getAttribute("data-name");
      window.open(`mailto:${contacts[name].email}`)
    })
  });
})();

$(document).ready(function() {
    $('.year_2022').click(function() {
      $('.year_2022').addClass('selected');
      $('.year_2023').removeClass('selected'); 
      $('.year_2024').removeClass('selected'); 
      $('.board-2023').addClass('hidden'); 
      $('.board-2022').removeClass('hidden');
      $('.board-2024').addClass('hidden'); 
    });

    $('.year_2023').click(function() {
      $('.year_2023').addClass('selected');
      $('.year_2022').removeClass('selected');
      $('.board-2022').addClass('hidden'); 
      $('.board-2023').removeClass('hidden');
      $('.board-2024').addClass('hidden');
      $('.year_2024').removeClass('selected');
    });
    $('.year_2024').click(function() {
      $('.year_2024').addClass('selected');
      $('.year_2023').removeClass('selected');
      $('.year_2022').removeClass('selected');
      $('.board-2024').removeClass('hidden'); 
      $('.board-2023').addClass('hidden');
      $('.board-2022').addClass('hidden');
    });
  });

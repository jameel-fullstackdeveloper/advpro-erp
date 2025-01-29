/*
Template Name: QuickERP - Cloud based ERP
Author: Themesbrand
Version: 4.1.0
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Common Plugins Js File
*/

//Common plugins
/*if(document.querySelectorAll("[toast-list]") || document.querySelectorAll('[data-choices]') || document.querySelectorAll("[data-provider]")){
  document.writeln("<script type='text/javascript' src='https://cdn.jsdelivr.net/npm/toastify-js'></script>");
  document.writeln("<script type='text/javascript' src='build/libs/choices.js/public/assets/scripts/choices.min.js'></script>");
  document.writeln("<script type='text/javascript' src='build/libs/flatpickr/flatpickr.min.js'></script>");
}*/

if(document.querySelectorAll("[toast-list]").length || document.querySelectorAll('[data-choices]').length || document.querySelectorAll("[data-provider]").length){
    document.writeln("<script type='text/javascript' src='https://cdn.jsdelivr.net/npm/toastify-js'><\/script>");
    document.writeln("<script type='text/javascript' src='{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}'><\/script>");
    document.writeln("<script type='text/javascript' src='{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}'><\/script>");
  }

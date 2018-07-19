@extends('layout')

@section('content')
	<h1 class="text-center">Ongoing Tournaments</h1>

	<div class="album py-5 bg-light text-white">
	   <div class="container">
	      <div class="row">
	         <div class="col-md-4">
	            <div class="card mb-4 box-shadow bg-info">
	               <!--<img class="card-img-top" src="{{ asset('imgs/na_lcs_icon.png') }}" alt="Card image cap">-->
  				   <div class="card-header">LOL</div>
	               <div class="card-body">
	                  <h5 class="card-title">NA LCS Summer 2018</h5>
	                  <p class="card-text">The European League Championship Series is the premier tournament for western
	                   League of Legends. It is hosted, broadcasted and organized by Riot Games.</p>	                  
	                   <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm"><a href="esportsViewers/lol">View</a></button>
	                        <!--<button type="button" class="btn btn-sm">Edit</button>-->
	                     </div>
	                     <small>June 16 2018 - Sept 9 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-info">
	               <!--<img class="card-img-top" src="{{ asset('imgs/eu_lcs_icon.png') }}" alt="Card image cap">-->
				   <div class="card-header">LOL</div>   
	               <div class="card-body">
	                  <h5 class="card-title">EU LCS Summer 2018</h5>
	                  <p class="card-text">The European League Championship Series is the premier tournament for western
	                   League of Legends. It is hosted, broadcasted and organized by Riot Games.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm"><a href="esportsViewers/lol">View</a></button>
	                        <!--<button type="button" class="btn btn-sm">Edit</button>-->
	                     </div>
	                     <small>June 16 2018 - Sept 9 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-danger">
  				   <div class="card-header">CS:GO</div>
	               <div class="card-body">
	                  <h5 class="card-title">ELEAGUE CS:GO Premier 2018</h5>
	                  <p class="card-text">The world's most elite counter strike teams compete in an exciting, live event format at the heart of Georgia.
	                  Eight teams are invited and will compete for the $1m prize purse.
	                  </p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>July 21 2018 - July 29 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-danger">
	               <!--<img class="card-img-top" data-src="holder.js/100px225?theme=thumb&bg=55595c&fg=eceeef&text=Thumbnail" alt="Card image cap">-->
  				   <div class="card-header">CS:GO</div>
	               <div class="card-body">
	                  <h5 class="card-title">Faceit Major</h5>
	                  <p class="card-text">The North American League Championship Series for League of Legends.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>Sept 12 2018 - Sept 23 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-success">
  				   <div class="card-header">Dota2</div>
	               <div class="card-body">
	                  <h5 class="card-title">The International 2018</h5>
	                  <p class="card-text">The North American League Championship Series for League of Legends.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>August 20 2018 - August 25 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-warning">
  				   <div class="card-header">Overwatch</div>
	               <div class="card-body">
	                  <h5 class="card-title">Overwatch League Playoffs</h5>
	                  <p class="card-text">The North American League Championship Series for League of Legends.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>July 11 2018 - July 28 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-danger">
  				   <div class="card-header">CS:GO</div>
	               <div class="card-body">
	                  <h5 class="card-title">IEM Shangai 2018</h5>
	                  <p class="card-text">The North American League Championship Series for League of Legends.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>August 1 2018 - August 6 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	         <div class="col-md-4">
	            <div class="card mb-4 text-white box-shadow bg-warning">
  				   <div class="card-header">Overwatch</div>
	               <div class="card-body">
	                  <h5 class="card-title">Overwatch World Cup 2018</h5>
	                  <p class="card-text">The North American League Championship Series for League of Legends.</p>
	                  <div class="d-flex justify-content-between align-items-center">
	                     <div class="btn-group">
	                        <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
	                        <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
	                     </div>
	                     <small>November 2 2018 - November 3 2018</small>
	                  </div>
	               </div>
	            </div>
	         </div>
	      </div>
	   </div>
	</div>

@stop
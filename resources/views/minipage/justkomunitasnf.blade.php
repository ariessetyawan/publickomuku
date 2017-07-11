		<div class="row">
			<div style="height:300px;background-image: url('img/komunitas/devider.png');"><center><font style="padding-left:250px;font-family: 'Rancho', cursive;color:black;font-size:35px">Yuk. Lihat dan join ke komunitas yang kece dibawah ini</font></center></div>
			<section id="grid" class="grid clearfix" style="margin-top:-60px">
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/1.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Crystalline</h2>
							<p>Soko radicchio bunya nuts gram dulse.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/3.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Cacophony</h2>
							<p>Two greens tigernut soybean radish.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/5.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Languid</h2>
							<p>Beetroot water spinach okra water.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/7.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Serene</h2>
							<p>Water spinach arugula pea tatsoi.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/2.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Nebulous</h2>
							<p>Pea horseradish azuki bean lettuce.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/4.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Iridescent</h2>
							<p>A grape silver beet watercress potato.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/6.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Resonant</h2>
							<p>Chickweed okra pea winter purslane.</p>
						</figcaption>
					</figure>
				</a>
				<a href="#" data-path-hover="m 180,34.57627 -180,0 L 0,0 180,0 z">
					<figure>
						<img src="img/komunitas/8.png" />
						<svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 180,160 0,218 0,0 180,0 z"/></svg>
						<figcaption>
							<h2>Zenith</h2>
							<p>Salsify taro catsear garlic gram.</p>
						</figcaption>
					</figure>
				</a>
			</section>
			
		</div>
<script>
! function() {
    function t() {
        var t = 250,
            e = mina.easeinout;
        [].slice.call(document.querySelectorAll("#grid > a")).forEach(function(n) {
            var o = Snap(n.querySelector("svg")),
                a = o.select("path"),
                i = {
                    from: a.attr("d"),
                    to: n.getAttribute("data-path-hover")
                };
            n.addEventListener("mouseenter", function() {
                a.animate({
                    path: i.to
                }, t, e)
            }), n.addEventListener("mouseleave", function() {
                a.animate({
                    path: i.from
                }, t, e)
            })
        })
    }
    t()
}()
</script>
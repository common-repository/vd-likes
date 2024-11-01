(function ($) {
		
        $('.vd_like_link').on('click', function () {
			var $this=$(this);			
			var pid = $(this).attr('data-id');
			var obj;		
			var old_up_like = $this.siblings('span.vd_up_count').text();
			var old_down_like = $this.siblings('span.vd_down_count').text();
			$.ajax({
				url:vd_likes_obj.ajaxurl,
				data:{'action':'vd_like','pid':pid},
				dataType:'json',
				success:function(data){							
					var new_up_like =data.like;
					var new_down_like =data.downlike;
					if(new_up_like>old_up_like){
						
						$this.siblings('span.vd_up_count').text(data.like);
						$this.find('i').removeClass('icon-thumbs-up');
						$this.find('i').addClass('icon-thumbs-up-alt');
					}
					if(new_down_like>old_down_like){
						
						$this.siblings('span.vd_down_count').text(data.downlike);
						$this.siblings('a.vd_downlike_link').find('i').removeClass('icon-thumbs-down-alt');
						$this.siblings('a.vd_downlike_link').find('i').addClass('icon-thumbs-down');
					}					
				}
			});
        });        
          $('.vd_downlike_link').on('click', function () {		
			
			var pid = $(this).attr('data-id');			
			var $this = $(this);
			var old_up_like = $this.siblings('span.vd_up_count').text();
			var old_down_like = $this.siblings('span.vd_down_count').text();
			$.ajax({
				url:vd_likes_obj.ajaxurl,
				dataType:'json',
				data:{'action':'vd_downlike','pid':pid},
				success:function(data){		
					
				
					
					var new_up_like =data.like;
					var new_down_like =data.downlike;
					if(new_up_like<old_up_like){
						
						$this.siblings('span.vd_up_count').text(data.like);
						$this.siblings('a.vd_like_link').find('i').removeClass('icon-thumbs-up-alt');
						$this.siblings('a.vd_like_link').find('i').addClass('icon-thumbs-up');
					}
					if(new_down_like<old_down_like){
						
						$this.siblings('span.vd_down_count').text(data.downlike);
						$this.find('i').removeClass('icon-thumbs-down');
						$this.find('i').addClass('icon-thumbs-down-alt');
					}
				
				}
			});
        });       
        
}( jQuery));

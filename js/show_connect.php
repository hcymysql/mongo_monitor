<?php 

// 默认展示近12个小时的曲线图
$interval_time = isset($interval_time) ? $interval_time : 'DATE_SUB(now(),interval 12 hour)'; 

?>

    <script type="text/javascript">
	      var  myChart = echarts.init(document.getElementById('connect'),'shine');
              var arr1=[],arr2=[],arr3=[];
              function arrTest(){
                $.ajax({
                  type:"post",
                  async:false,
		  url:"get_data/db_connect_getdata.php?fn=index&host=<?php echo $host;?>&port=<?php echo $port;?>&tag=<?php echo $tag;?>&interval_time=<?php echo $interval_time;?>",
                  data:{},
                  dataType:"json",
                  success:function(result){
                    if (result) {
                      for (var i = 0; i < result.length; i++) {
                          arr1.push(result[i].create_time);
						  arr2.push(result[i].threads_connected);
                          arr3.push(result[i].available_connected);
                      }
                    }
                  }
                })
                return arr1,arr2,arr3;
              }
              arrTest();

              var  option = {
					title: {
						text: '连接数图表'
					},
                    tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'cross',
						        //type:'category',
							label: {
								backgroundColor: '#6a7985'
							}
						}
                    },
                    legend: {
                       data:['活动连接数','可用连接数']
                    },
					grid: {
						left: '3%',
						right: '4%',
						bottom: '3%',
						containLabel: true
					},
                    xAxis : [
                        {
                            type : 'category',
							boundaryGap : false,
                            data : arr1
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
							axisLabel: {
								formatter:'{value}(个)'
                            }
			            }
                    ],

		    dataZoom: [
      			  {   // 这个dataZoom组件，默认控制x轴。
   			        //type: 'inside',
            			type: 'slider', // 这个 dataZoom 组件是 slider 型 dataZoom 组件
				//inverse: true,
            			start: 100,      // 左边在 10% 的位置。
            			end: 80         // 右边在 60% 的位置。
       		           }
    		    ],

		    grid:{
			    x2: 60 ,
       			    bottom: "70px"
       		    },

                    series : [
                        {
							name:'活动连接数',
							type:'line',
							stack: '个',
							label: {
								normal: {
								show: false,
								position: 'top'
							}
						},
							areaStyle: {normal: {}},
							data:arr2
						},
					   {
							name:'可用连接数',
							type:'line',
							stack: '个',
							areaStyle: {},
							data:arr3
					   }
                    ]
                };
                // 为echarts对象加载数据
                myChart.setOption(option);
            // }
    </script>

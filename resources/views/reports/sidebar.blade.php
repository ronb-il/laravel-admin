@foreach($reports as $key => $report)
     @if(isset($report['sub']))
            
            <li>
                <a class="accordion-toggle collapsed" data-toggle="collapse" data-target="#{{str_replace(' ','-',$report['title'])}}" style="content:"\e114">{{$report['title']}}</a>
            </li>
            <ul class="nav nav-list collapse" id="{{str_replace(' ','-',$report['title'])}}" style="padding-left :13px">
                 @foreach($report['sub'] as  $subKey => $subReport)
                     @if(isset($subReport['sub']))
                        <li>
                            <a class="accordion-toggle collapsed" data-toggle="collapse" data-target="#{{str_replace(' ','-',$subReport['title'])}}" style="content:"\e114">{{$subReport['title']}}</a>
                        </li>
                        <ul class="nav nav-list collapse" id="{{str_replace(' ','-',$subReport['title'])}}" style="padding-left :13px">
                            @foreach($subReport['sub'] as  $subsubKey => $subsubReport)

                                <li><a class="fkclss-reportlink fkclss-reportlink-{{ $subsubKey }}" href="{{ url("/reports/$subsubKey") }}?{{ Request::getQueryString() }}">
                                        {{ $subsubReport['title'] }}</a></li>
                            @endforeach
                        </ul>
                     @else
                        <li><a class="fkclss-reportlink fkclss-reportlink-{{ $subKey }}" href="{{ url("/reports/$subKey") }}?{{ Request::getQueryString() }}">
                              {{ $subReport['title'] }}</a></li>
                     @endif
                 @endforeach

             <!--08-09-16 : This Ab report will be deprecated soon-->
<!--                     @if($report['title'] == 'AB-Testing')
                    @if (Gate::allows('view', Resource::get('am-reports')))
                        <li><a href="{{ url("/abreport") }}?{{ Request::getQueryString() }}">AB Report</a></li>
                    @endif
                @endif -->
            </ul>
     @else
            <li><a class="fkclss-reportlink fkclss-reportlink-{{ $key }}" href="{{ url("/reports/$key") }}?{{ Request::getQueryString() }}">{{ $report['title'] }}</a></li>
     @endif
@endforeach
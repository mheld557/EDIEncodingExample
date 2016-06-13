@extends('layouts.app')

@section('content')
<div class="container">
	<div class="col-sm-offset-2 col-sm-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				Purchase Order in edi 850 format
			</div>
			<div class="ediText">
				<table width="800">
					@foreach ($ediData as $ediLine)   
						<tr><td>{{ $ediLine }}</td></tr>
					@endforeach
				</table>
			</div>
		</div>
	</div>
</div>
@endsection
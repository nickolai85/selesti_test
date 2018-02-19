@extends ('layouts.app')

@section('content')

    <div class="col-sm-6">
        <table class="table">
            <thead>
            <tr>
                <th>OrderID</th>
                <th>HasCustomerPaid</th>
                <th>CustomerName</th>
                <th>CustomerEmail</th>
                <th>Export CSV</th>
            </tr>
            </thead>
            @foreach($orders as $order)

                <tr>
                    <td><a href="{{route('CSVorder', $order['id'])}}">{{$order['id']}}</a></td>
                    <td>{{$order['isPaidFor']}}</td>
                    <td>{{$order['name']}}</td>
                    <td>{{$order['email']}}</td>
                    <td><a href="{{route('CSVorder', $order['id'])}}">Export</a></td>
                </tr>
            @endforeach

        </table>
        <a href="{{route('CSVorders')}}">Export Orders to CSV</a>
    </div>
@stop
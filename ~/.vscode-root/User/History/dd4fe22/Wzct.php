<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tabla</title>
</head>
<body>
    <h1>Tabla de prueba</h1>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>name</th>
                <th>type</th>
                <th>status</th>
                <th>cpu</th>
                <th>maxcpu</th>
                <th>mem</th>
                <th>maxmem</th>
                <th>netin</th>
                <th>netout</th>
                <th>disk</th>
                <th>diskread</th>
                <th>diskwrite</th>
                <th>uptime</th>
                <th>updated_at</th>
                <th>created_at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $dato)
            <tr>
                <td>{{ $dato->id }}</td>
                <td>{{ $dato->name }}</td>
                <td>{{ $dato->type }}</td>
                <td>{{ $dato->status }}</td>
                <td>{{ $dato->cpu }}</td>
                <td>{{ $dato->maxcpu }}</td>
                <td>{{ $dato->mem }}</td>
                <td>{{ $dato->maxmem }}</td>
                <td>{{ $dato->netin }}</td>
                <td>{{ $dato->netout }}</td>
                <td>{{ $dato->disk }}</td>
                <td>{{ $dato->diskread }}</td>
                <td>{{ $dato->diskwrite }}</td>
                <td>{{ $dato->uptime }}</td>
                <td>{{ $dato->updated_at }}</td>
                <td>{{ $dato->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
        
</body>
</html>


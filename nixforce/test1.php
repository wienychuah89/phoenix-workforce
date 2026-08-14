<script>
let numbers =[23,65,12,77,3];
let max = numbers[0];
for (let i = 1; i < numbers.length; i++) {
    if (numbers[i] > max) {
        max = numbers[i];
    }
}
alert("Maximum value is: " + max);


</script>
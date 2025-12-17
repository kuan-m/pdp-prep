package utils

import (
	"math"
)

// ComputeHeavy performs heavy computation (100,000 iterations)
// Equivalent to PHP test.php
func ComputeHeavy() float64 {
	result := 0.0
	for i := 0; i < 100000; i++ {
		result += math.Sqrt(float64(i))*math.Sin(float64(i)) + math.Cos(float64(i))
	}
	return result
}

// ComputeLight performs light computation (10,000 iterations)
// Equivalent to PHP test-light.php
func ComputeLight() float64 {
	result := 0.0
	for i := 0; i < 10000; i++ {
		result += math.Sqrt(float64(i))*math.Sin(float64(i)) + math.Cos(float64(i))
	}
	return result
}

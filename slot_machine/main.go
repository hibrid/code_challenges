package main

import (
	"bufio"
	"fmt"
	"os"
	"sort"
	"strconv"
)

func getMyMinimumStops(spins []string) int {
	var myMins = make(map[int]int)
	for _, spin := range spins {
		var spinSlice []int
		for _, number := range spin {
			i, err := strconv.Atoi(string(number))
			if err != nil {
				panic(err)
			}
			spinSlice = append(spinSlice, i)
		}
		sort.Ints(spinSlice)
		for position, number := range spinSlice {
			if value, ok := myMins[position]; ok {
				if value < number {
					myMins[position] = number
				}
			} else {
				myMins[position] = number
			}
		}
	}
	var total int
	for _, number := range myMins {
		total += number
	}
	return total
}

func main() {

	scn := bufio.NewScanner(os.Stdin)

	fmt.Println("Enter number of lines to parse:")
	var lines []string
	var numberOfThingsToParse int64
	for scn.Scan() {
		line := scn.Text()
		if len(line) == 1 {
			// Group Separator (GS ^]): ctrl-]
			if line[0] == '\x1D' {
				break
			}
		}
		lines = append(lines, line)
		if len(lines) == 1 {
			i, err := strconv.ParseInt(lines[0], 10, 64)
			if err != nil {
				panic(err)
			}
			numberOfThingsToParse = i
		}
		if int64(len(lines)) == numberOfThingsToParse+1 {
			break
		}
	}

	if len(lines) > 0 {
		fmt.Println()
		fmt.Println("Result:")

		spins := getMyMinimumStops(lines[1:])
		fmt.Printf("Spins: %v", spins)

		fmt.Println()
	}

	if err := scn.Err(); err != nil {
		fmt.Fprintln(os.Stderr, err)
	}

}

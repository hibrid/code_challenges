# Rules
- This is a timed test Results must be emailed back within 4 hours of being sent to you
- Use the PaydateCalculatorInterface and create a class in PHP5 OOP called MyPaydateCalculator
- Given a paydate and a paydate model, MyPaydateCalculator must be able to return the next 10 paydates after today
- MyPaydateCalculator must run without generating errors or warnings
- A valid paydate cannot fall on today, a weekend or a holiday
- If a paydate falls on a weekend, increment the date by one day until a valid paydate is reached.
- If a paydate falls on a holiday, decrement the date by one day until a valid paydate is reached.
- Holiday adjustments takes precedence over weekend adjustments
- The initial paydate given to your class should not be adjusted, even if it falls on a weekend or a holiday
- Part of the challenge is to do this unguided other than the specification, if necessary

# Holidays

`$holidays = ['01-01-2014','20-01-2014','17-02-2014','26-05-2014','04-07-2014','01-09-2014','13-10-2014','11-11-2014','27-11-2014','25-12-2014','01-01-2015','19-01-2015','16-02-2015','25-05-2015','03-07-2015','07-09-2015','12-10-2015','11-11-2015','26-11-2015','25-12-2015'];`

(feel free to adjust to current year)

# Paydate Models 
**MONTHLY** - A person is paid on the same day of the month every month, for instance, 1/17/2012 and 2/17/2012

**BIWEEKLY** - A person is paid on the same day of the week every other week, for instance, 4/6/2012 and 4/20/2012

**WEEKLY** - A person is paid on the same day of the week every week, for instance 4/9/2012 and 4/16/2012

# Structure
- ./v1 - has what I submitted and what I completed in the time allotted
- ./v2 - has what I wish I had time to finish 

# My Thoughts
This was an interesting code challenged. It had been about two years since I'd done any PHP so I was slower than normal. That said, I wanted to make sure that things worked as expected so I added simpletest since I find it adequate and it's more lightweight than phpunit.

It wasn't clear what should happen if a paydate lands on a weekend and the next available date is a holiday. It does say holiday rules takes precendence but this is too vague. In v1, I was running out of time so I took the easier interpretation and applied to decrement or increment rules based on the potential paydate. It chooses the next available date without regard to why the other dates were failing.

One thing I missed, because I was rushing, was to add the rule to begin date checking if a paydate is "today" and not a weekend or holiday.

In the v2 version, I made so that if the weekend rule applies, it considers why the evaluated dates are failing and if the next possible date, not a weekend, is a holiday, then apply the holiday logic.

For the arbitrary rule that a paydate can't be "today," simply continue (increment or decrement) based on the other rules that may already apply. Otherwise, apply the holiday logic and decrement.

The interface leaves a lot to be desired and I took the liberty to add type hinting. I didn't dare modify it more but my biggest "beef" with it is that it forces the handing of strings and the same date object is created multiple times. I realize this is a micro-optimization considering the likely data size. The rule that no warning or errors can't be returned I think is meant for things like deprecation warnings but I took it literally and things fail silently. 

One thing that may be interesting is a limit on the number of paydates required but again, no one asked for it and I guess someone could put a huge number in there but that's unlikely.

The last requirement that I thought was weird saying that it should return the next 10 dates but the interface gives you the option to speficy how many dates you want. Techincally it "can" return the next ten dates. This requirement is also at odds with the comment on the interface for the calculateNextPaydates method. It says "from today." I took that to mean that we should include the first pay date in the list of paydates and if not, we can easily remove that. Again, vague and I'd have to chat with the product owner/team.
